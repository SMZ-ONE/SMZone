<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Setting;
use App\Models\AiLearning;
use App\Models\BrandProfile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AiContentService
{
    /**
     * Son çağrıda bir hata/fallback olduysa burada tutulur.
     * Caption metnine ASLA karışmaz - çağıran taraf (örn. AiWriter)
     * getLastError() ile okuyup debug log'una ekleyebilir.
     */
    protected ?string $lastError = null;

    /**
     * generateWithGemini() içinde caption için gerçekten kullanılan (hedef dile
     * çevrilmiş) isim/açıklama burada tutulur. Önceden bu bilgi dışarı hiç
     * çıkmıyordu - çağıran taraf (AiWriter) hashtag üretimi için hâlâ ham/
     * çevrilmemiş metni kullanıyordu, bu da caption Almanca olsa bile hashtag
     * prompt'unun Türkçe/karışık dille beslenmesine yol açıyordu.
     */
    protected ?string $lastTranslatedName = null;
    protected ?string $lastTranslatedDescription = null;

    public function getLastError(): ?string
    {
        return $this->lastError;
    }

    public function getLastTranslatedName(): ?string
    {
        return $this->lastTranslatedName;
    }

    public function getLastTranslatedDescription(): ?string
    {
        return $this->lastTranslatedDescription;
    }

    /**
     * DI ile (instance olarak) çağrılan asıl metod.
     * $data: ['name','description','ingredients','category','url','platform','tone','lang','length']
     */
    public function generateFromText(array $data): string
    {
        $this->lastError = null;
        $this->lastTranslatedName = null;
        $this->lastTranslatedDescription = null;

        $name = trim((string) ($data['name'] ?? '')) ?: 'Product';
        $description = (string) ($data['description'] ?? '');
        $ingredients = (string) ($data['ingredients'] ?? '');
        $category = (string) ($data['category'] ?? '');
        $url = $data['url'] ?? null;
        $platform = (string) ($data['platform'] ?? 'instagram');
        $tone = (string) ($data['tone'] ?? 'premium');
        $lang = (string) ($data['lang'] ?? 'de');
        $length = (string) ($data['length'] ?? 'medium');

        $geminiKey = Setting::get('gemini_api_key');

        if (!$geminiKey) {
            $this->lastError = 'Gemini API key tanımlı değil, fallback kullanıldı.';
            // Çeviri hiç yapılmadı - ham metinler bu şekilde kalıyor, çağıran taraf
            // (hashtag üretimi) en azından caption ile TUTARLI bir metinle çalışsın.
            $this->lastTranslatedName = $name;
            $this->lastTranslatedDescription = $description;
            return $this->generateFallback($name, $description, $ingredients);
        }

        try {
            return $this->generateWithGemini($name, $description, $ingredients, $category, $url, $platform, $tone, $lang, $length, $geminiKey);
        } catch (\Throwable $e) {
            // Hata SADECE loglanır ve $lastError'a yazılır - caption metnine ASLA karışmaz.
            Log::warning('Gemini failed: '.$e->getMessage());
            $this->lastError = $e->getMessage();
            // Çeviri denendiyse bile sonuçlanmadı (exception) - fallback caption ham
            // metinle üretildiği için burada da tutarlılık adına ham metinler saklanıyor.
            $this->lastTranslatedName = $name;
            $this->lastTranslatedDescription = $description;
            return $this->generateFallback($name, $description, $ingredients);
        }
    }

    /**
     * Geriye dönük uyumluluk: Product nesnesiyle static çağıran eski kod varsa
     * (örn. bulk-generate araçları) bozulmasın diye korunuyor.
     */
    public static function generateCaption(Product $product, string $platform = 'instagram', string $tone = 'premium', string $lang = 'de', string $length = 'medium'): string
    {
        return app(self::class)->generateFromText([
            'name' => $product->name,
            'description' => $product->short_description ?? $product->description ?? '',
            'ingredients' => is_array($product->ingredients) ? implode(', ', $product->ingredients) : ($product->ingredients ?? ''),
            'category' => $product->category ?? '',
            'url' => null,
            'platform' => $platform,
            'tone' => $tone,
            'lang' => $lang,
            'length' => $length,
        ]);
    }

    private function generateWithGemini(
        string $name,
        string $description,
        string $ingredients,
        string $category,
        ?string $url,
        string $platform,
        string $tone,
        string $lang,
        string $length,
        string $apiKey
    ): string {
        // Öğrenen mantık - son 5 düzenleme
        $learnings = AiLearning::where('user_id', auth()->id() ?? 1)->latest()->take(5)->get();
        $learningContext = "";
        if ($learnings->count() > 0) {
            $learningContext = "\nKULLANICI TARZI (önceki düzeltmelerinden öğrendiğin):\n";
            foreach ($learnings as $l) {
                if ($l->edited_content) {
                    // mb_substr: çok baytlı karakterleri (ö, ü, ß, emoji vb.) ortadan kesmemek için
                    $before = mb_substr($l->original_content, 0, 100);
                    $after = mb_substr($l->edited_content, 0, 100);
                    $learningContext .= "- Önceki: '{$before}' -> Kullanıcı düzeltti: '{$after}'\n";
                }
            }
        }

        // Brand Studio profili - marka sesi, yasaklı kelimeler, tercih edilen hashtag/keyword'ler.
        // Aktif profil yoksa (kullanıcı Brand Studio'yu hiç doldurmadıysa) tüm bölümler sessizce boş kalır.
        $brand = BrandProfile::activeForUser(auth()->id() ?? 1);
        $brandVoiceBlock = '';
        $brandStoryBlock = '';
        $brandKeywordsBlock = '';
        $forbiddenWordsBlock = '';
        $extraInstructionsBlock = '';
        $websiteUrl = $brand->website_url ?? null;

        if ($brand) {
            if (!empty($brand->brand_voice)) {
                $brandVoiceBlock = "\nMARKENSTIMME (schreibe IMMER in diesem Stil): {$brand->brand_voice}\n";
            }
            if (!empty($brand->brand_story)) {
                $brandStoryBlock = "\nMARKENHINTERGRUND (nur Kontext - NICHT wörtlich kopieren, keine neuen Fakten daraus erfinden): {$brand->brand_story}\n";
            }
            if (!empty($brand->brand_keywords)) {
                $keywords = implode(', ', $brand->brand_keywords);
                $brandKeywordsBlock = "\nMARKEN-KEYWORDS (nur verwenden wenn zum Produkt wirklich passend, NICHT erzwingen): {$keywords}\n";
            }
            if (!empty($brand->forbidden_words)) {
                $forbidden = implode(', ', $brand->forbidden_words);
                $forbiddenWordsBlock = "\nVERBOTENE WÖRTER (diese NIEMALS im Text verwenden, unter keinen Umständen): {$forbidden}\n";
            }
            if (!empty($brand->extra_instructions)) {
                $extraInstructionsBlock = "\nZUSÄTZLICHE MARKEN-ANWEISUNG: {$brand->extra_instructions}\n";
            }
        }

        // 'tr' desteklenmiyor - AiWriter'daki lang Select'i de sadece de/en sunuyor.
        $langMap = ['de'=>'Deutsch','en'=>'English'];
        $platformMap = ['instagram'=>'Instagram Caption','tiktok'=>'TikTok Caption','facebook'=>'Facebook Post'];
        $lengthMap = [
            'short' => 'Sehr kurz, 2-3 Sätze',
            'medium' => 'Mittel, 4-6 Sätze',
            'long' => 'Ausführlich, 8-10 Sätze'
        ];

        $langLabel = $langMap[$lang] ?? 'Deutsch';
        $platformLabel = $platformMap[$platform] ?? 'Instagram Caption';
        $lengthLabel = $lengthMap[$length] ?? $lengthMap['medium'];

        // Ana caption promptu "yaz + çevir + markaya uy" gibi çok işi aynı anda istediği için
        // model bazen çeviriyi atlıyordu (ör. Türkçe kalıyordu). Bunu ayrı, tek işi olan
        // küçük çağrılarla önceden hallediyoruz - odaklı görevlerde model çok daha
        // güvenilir çalışıyor. Önceden SADECE isim çevriliyordu; açıklama (ve varsa
        // ingredients) çevrilmeden ana prompta gidiyordu, bu da özellikle scrape edilen
        // uzun/yabancı dildeki açıklamalarda modelin çeviriyi atlamasına ve caption'a
        // yabancı dil karışmasına yol açıyordu. Her ikisi de artık aynı şekilde,
        // cache'li olarak (aynı metin/dil tekrar geldiğinde API'ye gitmez) önceden çevriliyor.
        $name = $this->normalizeToLanguage($name, $langLabel, $apiKey);
        $description = $this->normalizeToLanguage($description, $langLabel, $apiKey);
        if (trim($ingredients) !== '') {
            $ingredients = $this->normalizeToLanguage($ingredients, $langLabel, $apiKey);
        }

        // Hashtag üretimi gibi ikincil işlemler caption'la AYNI (çevrilmiş) metni
        // kullanabilsin diye burada dışarı açılıyor - bkz. getLastTranslatedName/Description().
        $this->lastTranslatedName = $name;
        $this->lastTranslatedDescription = $description;

        $urlLine = $url ? "Quelle URL: {$url}" : '';

        // NOT: Bu ternary bilinçli olarak heredoc'un DIŞINDA hesaplanıyor.
        // PHP'nin {$...} (curly/complex) string interpolasyon sözdizimi ternary (?:)
        // ifadelerini desteklemiyor - {$x ? 'a' : 'b'} heredoc/double-quoted string
        // içinde her zaman "unexpected token '?'" parse hatası verir. Bu satır önceden
        // doğrudan heredoc içindeydi ve production'da 500 hatasına yol açıyordu.
        $ctaHint = $websiteUrl
            ? " - falls ein CTA-Link sinnvoll ist, nutze AUSSCHLIESSLICH \"{$websiteUrl}\", niemals eine andere Domain"
            : '';

        $prompt = <<<PROMPT
WICHTIG: Antworte AUSSCHLIESSLICH auf {$langLabel} - der GESAMTE Text, auch wenn die Produktdaten unten in einer anderen Sprache formuliert sind.

Du bist ein ehrlicher Produkt-Texter. Du darfst KEINE FAKTEN erfinden, aber du MUSST alles ins {$langLabel} übersetzen.

PRODUKT-DATEN (NUR DIESE FAKTEN verwenden, aber ins {$langLabel} übersetzen falls nötig):
Name: {$name}
Kategorie: {$category}
Beschreibung: {$description}
Ingredients: {$ingredients}
{$urlLine}
{$brandVoiceBlock}{$brandStoryBlock}{$brandKeywordsBlock}{$forbiddenWordsBlock}{$extraInstructionsBlock}
{$learningContext}

Aufgabe: Schreibe eine {$platformLabel} auf {$langLabel}, Ton: {$tone}, Länge: {$lengthLabel}.

STRENGE REGELN:
- FAKTEN NICHT ERFINDEN: Wenn Herkunft/Brand/Standort nicht in den Produkt-Daten steht, erwähne sie NICHT - auch nicht aus der Markenstimme/-story ableiten
- Wenn nur ein Link aus einem anderen Land gegeben ist, tue NICHT so als käme das Produkt von woanders
- Verwende NUR die gegebene Beschreibung und Ingredients als Fakten-Basis
- Wenn Ingredients leer, erwähne keine Ingredients
- Kein Link/URL als CTA hinzufügen, außer es ist EXPLIZIT als "Website" unten angegeben oder steht in den Produkt-Daten/URL{$ctaHint}
- KEINE Hashtags in den Caption-Text einfügen - Hashtags werden separat generiert und automatisch angehängt
- NIEMALS einen anderen Shop-, Seiten- oder Markennamen aus den Rohdaten übernehmen (z.B. wenn im Titel/URL ein fremder Shopname wie "XYZ Shop" auftaucht) - nutze NUR die Produkteigenschaften (Name, Wirkung, Ingredients), nicht den Namen der Quell-Website
- Max 1-2 Emojis, authentisch
- SPRACHE IST PFLICHT, NICHT OPTIONAL: Falls Produktname oder Beschreibung oben in einer anderen Sprache (z.B. Türkisch, Englisch) vorliegen, ÜBERSETZE sie sinngemäß ins {$langLabel}. Kopiere NIEMALS fremdsprachige Wörter oder Sätze unverändert in deinen Text - das gilt auch für einzelne Wörter im Produktnamen (z.B. "saç bakım kremi" wird zu "Haarpflegecreme", NICHT wörtlich übernommen)
- Am Ende nochmal Kontrolle: Ist WIRKLICH jedes Wort auf {$langLabel}? Wenn nicht, korrigiere es

Nur Caption, keine Erklärung.
PROMPT;

        $maxTokens = match($length) {
            'short' => 150,
            'long' => 700,
            default => 400,
        };

        $response = Http::timeout(20)
            ->withHeaders(['x-goog-api-key' => $apiKey])
            ->post(
                "https://generativelanguage.googleapis.com/v1beta/interactions",
                [
                    'model' => 'gemini-flash-lite-latest',
                    'input' => $prompt,
                    'generation_config' => ['max_output_tokens' => $maxTokens]
                ]
            );

        if (!$response->successful()) {
            throw new \Exception('API '.$response->status().' - '.$response->body());
        }

        $text = '';
        foreach ($response->json('steps', []) as $step) {
            if (($step['type'] ?? null) === 'model_output') {
                foreach ($step['content'] ?? [] as $content) {
                    if (($content['type'] ?? null) === 'text') {
                        $text .= $content['text'] ?? '';
                    }
                }
            }
        }

        if (!$text) {
            throw new \Exception('Bos cevap: '.json_encode($response->json()));
        }

        return $this->stripInlineHashtags(trim($text));
    }

    /**
     * Prompt kuralı ("hashtag ekleme") model tarafından her zaman uygulanmıyor -
     * özellikle gemini-flash-lite gibi küçük modellerde talimat takibi garanti değil.
     * Bu yüzden dönen metinden hashtag'leri burada, kod seviyesinde, garantili olarak
     * temizliyoruz - HashtagService'in ürettiği ayrı, kontrollü liste tek kaynak kalsın.
     */
    private function stripInlineHashtags(string $text): string
    {
        // Hashtag token'larını kaldır (Unicode-safe: umlaut'lu hashtag'ler de dahil)
        $cleaned = preg_replace('/#[\p{L}\p{N}_]+/u', '', $text);

        // Kalan fazladan boşlukları/satır sonlarını toparla
        $cleaned = preg_replace('/[ \t]+/', ' ', $cleaned);
        $cleaned = preg_replace('/\n{3,}/', "\n\n", $cleaned);
        $cleaned = preg_replace('/[ \t]+\n/', "\n", $cleaned);

        return trim($cleaned);
    }

    /**
     * Verilen metni hedef dile çevirir - tek işi bu olan küçük, odaklı bir çağrı.
     * Ana caption promptu bunu güvenilir yapmıyordu çünkü aynı anda birçok talimatla
     * (yaz + marka sesine uy + kural uygula + çevir) boğulmuş durumdaydı.
     * Hata olursa veya boş dönerse, sessizce orijinal metni kullanır - ana akışı bozmaz.
     */
    private function normalizeToLanguage(string $text, string $langLabel, string $apiKey): string
    {
        if (trim($text) === '') {
            return $text;
        }

        // Çeviriler zamanla değişmez - aynı metin/dil kombinasyonu tekrar geldiğinde
        // (ör. "Regenerate" veya aynı ürünle ikinci deneme) API'ye tekrar gitmeyelim.
        $cacheKey = 'text_translation:'.md5($text.'|'.$langLabel);

        return Cache::remember($cacheKey, now()->addDay(), function () use ($text, $langLabel, $apiKey) {
            // Kısa metinler (isim) için 100 token yeterliydi ama açıklama gibi uzun
            // metinlerde çeviri 100 tokende kesilebiliyordu - metin uzunluğuna göre
            // ölçekleniyor, mb_strlen kabaca token sayısına yakın bir üst sınır verir.
            $maxTokens = max(100, (int) ceil(mb_strlen($text) / 2));
            return $this->translateViaGemini($text, $langLabel, $apiKey, $maxTokens);
        });
    }

    private function translateViaGemini(string $text, string $langLabel, string $apiKey, int $maxTokens = 100): string
    {
        try {
            $prompt = "Übersetze den folgenden Text ins {$langLabel}, falls er nicht bereits auf {$langLabel} ist. "
                ."Wenn er bereits auf {$langLabel} ist, gib ihn unverändert zurück. "
                ."Antworte NUR mit dem übersetzten bzw. unveränderten Text - keine Anführungszeichen, keine Erklärung, keine zusätzlichen Wörter.\n\n"
                ."Text: {$text}";

            $response = Http::timeout(10)
                ->withHeaders(['x-goog-api-key' => $apiKey])
                ->post(
                    "https://generativelanguage.googleapis.com/v1beta/interactions",
                    [
                        'model' => 'gemini-flash-lite-latest',
                        'input' => $prompt,
                        'generation_config' => ['max_output_tokens' => $maxTokens],
                    ]
                );

            if (!$response->successful()) {
                return $text;
            }

            $translated = '';
            foreach ($response->json('steps', []) as $step) {
                if (($step['type'] ?? null) === 'model_output') {
                    foreach ($step['content'] ?? [] as $content) {
                        if (($content['type'] ?? null) === 'text') {
                            $translated .= $content['text'] ?? '';
                        }
                    }
                }
            }

            $translated = trim($translated, " \t\n\r\0\x0B\"'");

            return $translated !== '' ? $translated : $text;
        } catch (\Throwable $e) {
            Log::warning('Name translation failed: '.$e->getMessage());
            return $text;
        }
    }

    private function generateFallback(string $name, string $desc, string $ing): string
    {
        return "✨ {$name}\n\n{$desc}\n\n{$ing}";
    }
}
