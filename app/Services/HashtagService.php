<?php

namespace App\Services;

use App\Models\Setting;
use App\Models\BrandProfile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class HashtagService
{
    /**
     * Son çağrıda fallback'e düşüldüyse sebebi burada tutulur - AiContentService'teki
     * $lastError mekanizmasıyla aynı mantık. Önceden bu hiç görünür değildi, bu yüzden
     * "hep aynı sabit hashtag geliyor" şikayetinin sebebi (Gemini mi başarısız oluyor,
     * yoksa başka bir şey mi) teşhis edilemiyordu.
     */
    protected ?string $lastError = null;

    public function getLastError(): ?string
    {
        return $this->lastError;
    }

    /**
     * DI ile (instance olarak) çağrılan asıl metod.
     * Yeni parametreler ($ingredients, $caption, $lang) sona eklendi ve varsayılan
     * değerlere sahip - mevcut 3-parametreli çağrılar (ör. generateDachHashtags) kırılmaz.
     *
     * $excludeTags: "Regenerate Hashtags" gibi bir yeniden üretme isteğinde önceki
     * sonucu buraya vererek modelden FARKLI bir set istiyoruz. Aksi halde aynı prompt
     * Gemini'ye tekrar gidince model neredeyse birebir aynı listeyi döndürüyordu -
     * kullanıcıya "yenilenmiyor" gibi görünüyordu.
     */
    public function suggest(
        string $productName,
        string $description = '',
        string $category = '',
        string $ingredients = '',
        string $caption = '',
        string $lang = 'de',
        array $excludeTags = []
    ): array {
        $this->lastError = null;

        // Brand Studio "preferred_hashtags" artık HER seferinde zorla eklenmiyor -
        // bu, 31 sabit tag'in 20'lik limiti tek başına doldurup ürüne özgü AI
        // hashtag'lerine hiç yer bırakmamasına yol açıyordu. Bunun yerine bir
        // "havuz" olarak Gemini'ye veriliyor; model sadece ürüne gerçekten uyanları
        // seçip kendi ürettiği kategorilerle (DE/EN/ürüne özgü) harmanlıyor -
        // brand_keywords için AiContentService'te zaten kullanılan "öner ama
        // zorlama" yaklaşımıyla aynı mantık.
        $brand = BrandProfile::activeForUser(auth()->id() ?? 1);
        $preferredPool = ($brand && !empty($brand->preferred_hashtags))
            ? array_map('strtolower', $brand->preferred_hashtags)
            : [];

        return $this->generateTags($productName, $description, $category, $ingredients, $caption, $lang, $excludeTags, $preferredPool);
    }

    private function generateTags(
        string $productName,
        string $description,
        string $category,
        string $ingredients = '',
        string $caption = '',
        string $lang = 'de',
        array $excludeTags = [],
        array $preferredPool = []
    ): array {
        $geminiKey = Setting::get('gemini_api_key');
        if (!$geminiKey) {
            $this->lastError = 'Gemini API key tanımlı değil, fallback kullanıldı.';
            return $this->fallbackHashtags($category);
        }

        try {
            // Caption verilmişse promptta ayrı bir bölüm olarak ekleniyor - Gemini gerçekte
            // yazılan metne bakıp konuya/tona daha uygun, daha alakalı hashtag üretebilsin.
            $captionBlock = $caption !== '' ? "\nBereits geschriebener Caption-Text (für Kontext, nicht wiederholen):\n{$caption}\n" : '';
            $ingredientsLine = $ingredients !== '' ? "Ingredients: {$ingredients}\n" : '';

            // Regenerate durumunda önceki hashtag'leri açıkça yasaklıyoruz, yoksa aynı
            // deterministik prompt modelden neredeyse aynı listeyi geri getiriyordu.
            $excludeBlock = '';
            if (!empty($excludeTags)) {
                $excludeList = implode(', ', $excludeTags);
                $excludeBlock = "\nWICHTIG - ANDERE HASHTAGS FINDEN: Diese wurden bereits vorgeschlagen, verwende sie NICHT erneut und finde thematisch andere/alternative Tags: {$excludeList}\n";
            }

            $preferredBlock = '';
            if (!empty($preferredPool)) {
                $preferredList = implode(', ', $preferredPool);
                $preferredBlock = "\nMARKEN-HASHTAG-POOL (nur die verwenden, die WIRKLICH zu diesem Produkt passen - NICHT alle erzwingen, NICHT alle verwenden müssen, kann auch nur 2-3 oder gar keiner sein): {$preferredList}\n";
            }

            $prompt = <<<PROMPT
Produkt: {$productName}
Beschreibung: {$description}
Kategorie: {$category}
{$ingredientsLine}{$captionBlock}{$excludeBlock}{$preferredBlock}
Aufgabe: Generiere 15-20 relevante Hashtags für DACH Markt (Deutschland, Austria, Schweiz).
Caption-Sprache: {$lang} - berücksichtige das bei der Hashtag-Auswahl (z.B. bei englischsprachigem Caption mehr internationale Tags).
Mix aus:
- 5-6 Deutsch: #naturkosmetik #cleanbeautyde #hautpflege etc
- 5-6 English global: #naturalcosmetics #organicskincare #veganbeauty
- 3-4 Austria/Wien spezifisch: #naturkosmetikwien #wien etc (nur wenn sinnvoll)
- 2-3 Produkt-spezifisch (aus Ingredients/Caption abgeleitet, falls vorhanden)
- Passende Tags aus dem Marken-Hashtag-Pool (falls vorhanden und relevant, siehe oben)

Regeln:
- Kleinbuchstaben, ohne Leerzeichen
- Nur Hashtag, ein Hashtag pro Zeile, mit # beginnen
- Keine Erklärung, nur Liste
- 15-20 Stück
PROMPT;

            $response = Http::timeout(15)
                ->withHeaders(['x-goog-api-key' => $geminiKey])
                ->post(
                    "https://generativelanguage.googleapis.com/v1beta/interactions",
                    [
                        'model' => 'gemini-flash-lite-latest',
                        'input' => $prompt,
                        'generation_config' => [
                            'max_output_tokens' => 300,
                            // Varsayılan (düşük) temperature ile aynı prompt neredeyse
                            // aynı çıktıyı veriyordu - "regenerate" işe yaramıyormuş gibi
                            // görünüyordu. Yeniden üretimde daha çeşitli sonuç için
                            // temperature belirgin şekilde yükseltiliyor.
                            'temperature' => !empty($excludeTags) ? 1.0 : 0.7,
                        ],
                    ]
                );

            if (!$response->successful()) {
                throw new \Exception('Hashtag API '.$response->status().' - '.$response->body());
            }

            $text = '';
            foreach ($response->json('steps', []) as $step) {
                if (($step['type'] ?? null) === 'model_output') {
                    foreach ($step['content'] ?? [] as $content) {
                        if (($content['type'] ?? null) === 'text') $text .= $content['text'] ?? '';
                    }
                }
            }

            // \w, PHP'de /u modifiyeriyle bile Unicode-farkında olmadığı için Almanca
            // umlaut'lu hashtag'leri (#nachhaltigeschönheit gibi) yarım keserdi.
            // \p{L}\p{N}_ Unicode harf/rakam/alt çizgiyi doğru yakalıyor.
            preg_match_all('/#[\p{L}\p{N}_]+/u', $text, $matches);
            $tags = array_unique(array_map('strtolower', $matches[0] ?? []));

            // Regenerate isteğiyse ve model yine de bazı eski tag'leri tekrar ettiyse,
            // en azından bunları listeden düşürüyoruz - kullanıcı görünür bir fark görsün.
            if (!empty($excludeTags)) {
                $excludeLower = array_map('strtolower', $excludeTags);
                $tags = array_diff($tags, $excludeLower);
            }

            if (count($tags) < 5) {
                $this->lastError = 'Gemini yeterli hashtag üretmedi ('.count($tags).' adet), fallback kullanıldı. Ham yanıt: '.mb_substr($text, 0, 200);
                return $this->fallbackHashtags($category);
            }

            return array_slice(array_values($tags), 0, 25);

        } catch (\Throwable $e) {
            Log::warning('Hashtag gen failed: '.$e->getMessage());
            $this->lastError = $e->getMessage();
            return $this->fallbackHashtags($category);
        }
    }

    private function fallbackHashtags(string $category = ''): array
    {
        return [
            '#naturkosmetik', '#naturalcosmetics', '#organicskincare',
            '#vegankosmetik', '#cleanbeauty', '#greenbeauty',
            '#hautpflege', '#skincareroutine', '#naturkosmetikwien',
            '#biokosmetik', '#crueltyfree', '#ecofriendly',
            '#veganbeauty', '#nachhaltigeschoenheit', '#naturprodukte'
        ];
    }

    /**
     * Geriye dönük uyumluluk: eski static çağrıyı kullanan kod varsa bozulmasın diye.
     */
    public static function generateDachHashtags(string $productName, string $description = '', string $category = ''): array
    {
        return app(self::class)->suggest($productName, $description, $category);
    }
}
