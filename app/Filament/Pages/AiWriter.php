<?php

namespace App\Filament\Pages;

use App\Enums\ContentStatus;
use App\Models\BrandProfile;
use App\Models\ContentItem;
use App\Models\Product;
use App\Services\AiContentService;
use App\Services\AiLearningService;
use App\Services\HashtagService;
use App\Services\UrlScraperService;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Pages\Page;
use Filament\Notifications\Notification;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Log;
use BackedEnum;
use UnitEnum;

class AiWriter extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-sparkles';
    protected static string|UnitEnum|null $navigationGroup = 'SMZ ONE - AI';
    protected string $view = 'filament.pages.ai-writer';
    protected static ?string $title = 'AI Writer';
    protected static ?int $navigationSort = 1;

    public ?array $data = [];
    public ?string $generated = null;
    public ?string $debugLog = null;
    public array $suggestedHashtags = [];

    // generate() sırasında hesaplanan nihai isim/açıklama burada cache'lenir.
    // regenerateHashtags() tekrar scrape yapmadan bunları kullanır; aynı zamanda
    // persistDraft() içinde meta'ya yazılan bilgi ile tutarlılık sağlar.
    public string $lastFinalName = '';
    public string $lastFinalDesc = '';
    public string $brandDisplayName = '';

    public function mount(): void
    {
        $brand = BrandProfile::activeForUser();

        $this->form->fill([
            'product_id' => null,
            'product_url' => '',
            'custom_name' => '',
            'custom_description' => '',
            'platform' => 'instagram',
            'format' => 'post',
            'media' => null,
            'tone' => $brand?->tone_default ?? 'professional',
            'lang' => $brand?->lang_default ?? 'de',
            'length' => 'medium',
        ]);

        $this->brandDisplayName = $brand?->name ?? 'bycosmetiq';
    }

    /**
     * Canlı önizlemede gösterilecek görsel(ler) - öncelik sırası:
     * 1) Kullanıcının bu formda yüklediği medya (Storage::url() ile doğru
     *    disk URL'i üretiliyor - önceden asset('storage/'.$path) kullanılıyordu,
     *    bu disk ayarına göre yanlış/bozuk URL üretebiliyordu).
     * 2) Hiç medya yüklenmediyse, seçili üründen (varsa) Shopify görseli.
     */
    public function getPreviewImages(): array
    {
        $rawMedia = $this->data['media'] ?? null;
        $paths = is_array($rawMedia) ? array_values($rawMedia) : ($rawMedia ? [$rawMedia] : []);

        if (!empty($paths)) {
            return array_map(fn ($path) => \Illuminate\Support\Facades\Storage::disk('public')->url($path), $paths);
        }

        if (!empty($this->data['product_id']) && $product = Product::find($this->data['product_id'])) {
            return $product->image ? [$product->image] : [];
        }

        return [];
    }

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            // Shopify senkronundan (products:sync-shopify) gelen yerel kataloğu
            // aratıyor - seçilirse product_url/custom alanlarına gerek kalmadan
            // isim/açıklama/kategori doğrudan buradan kullanılır (bkz. generate()).
            Select::make('product_id')
                ->label('Product (from Shopify catalog)')
                ->placeholder('Search synced products...')
                ->searchable()
                ->nullable()
                ->getSearchResultsUsing(fn (string $search): array => Product::query()
                    ->where('is_active', true)
                    ->where('name', 'like', "%{$search}%")
                    ->limit(20)
                    ->pluck('name', 'id')
                    ->toArray())
                ->getOptionLabelUsing(fn ($value): ?string => Product::find($value)?->name)
            // Kataloğdan seçilmiş ürün varsa isim/açıklama alanlarına da otomatik
            // yansıtılıyor - hem şeffaf (ne gönderildiği görünür) hem düzenlenebilir
            // (generate() zaten custom_name/custom_description'ı her zaman öncelikli
            // okuyor, üstüne ekleme yapılırsa o dikkate alınır).
            ->live()
            ->afterStateUpdated(function ($state, callable $set) {
                if ($product = Product::find($state)) {
                    $set('custom_name', $product->name);
                    $set('custom_description', $product->description ?? '');
                }
            })
            ->columnSpanFull(),

            TextInput::make('product_url')
                ->label('Product URL')
                ->placeholder('https://... (ürün listede yoksa)')
                ->url()
                ->nullable()
                ->columnSpanFull(),

            TextInput::make('custom_name')
                ->label('Product Name')
                ->placeholder('e.g. Argan Oil 100ml')
                ->columnSpanFull(),

            Textarea::make('custom_description')
                ->label('Product Description')
                ->placeholder('What does it do? Who is it for? Benefits...')
                ->rows(4)
                ->columnSpanFull(),

            Select::make('platform')
                ->label('Platform')
                ->options(['instagram'=>'Instagram','tiktok'=>'TikTok','facebook'=>'Facebook'])
                ->default('instagram')
                ->required(),

            Select::make('format')
                ->label('Format')
                ->options([
                    'post' => 'Post',
                    'story' => 'Story',
                    'reels' => 'Reels',
                    'carousel' => 'Carousel',
                ])
                ->default('post')
                ->required()
                ->live()
                ->native(false),

            FileUpload::make('media')
                ->label('Media')
                ->disk('public')
                ->directory('content-media')
                ->multiple(fn (Get $get) => $get('format') === 'carousel')
                ->maxFiles(fn (Get $get) => $get('format') === 'carousel' ? 10 : 1)
                ->acceptedFileTypes(fn (Get $get) => $get('format') === 'reels' ? ['video/mp4'] : ['image/png', 'image/jpeg', 'image/webp'])
                ->helperText(fn (Get $get) => $get('format') === 'reels' ? 'Reels için video (mp4)' : ($get('format') === 'carousel' ? 'Carousel için birden fazla görsel (en fazla 10)' : 'Tek görsel'))
                ->live()
                ->columnSpanFull(),

            Select::make('tone')
                ->label('Tone')
                ->options([
                    'professional'=>'Professional - Expert & trustworthy',
                    'friendly'=>'Friendly - Warm & conversational',
                    'playful'=>'Playful - Fun & energetic',
                    'luxury'=>'Luxury - Premium & elegant',
                ])
                ->default('professional')
                ->required()
                ->native(false),

            // NOT: 'tr' bilinçli olarak yok. AiContentService'in $langMap'i şu an sadece
            // de/en çeviri talimatı içeriyor - Türkçe eklenirse oradaki map'e de eklenmeli,
            // yoksa dil karışması bug'ı geri döner.
            Select::make('lang')
                ->label('Language')
                ->options(['de'=>'Deutsch','en'=>'English'])
                ->default('de')
                ->required()
                ->native(false),

            Select::make('length')
                ->label('Length')
                ->options(['short'=>'Short','medium'=>'Medium','long'=>'Long'])
                ->default('medium')
                ->required()
                ->native(false),
        ])->statePath('data');
    }

    public function generate(AiContentService $aiService, HashtagService $hashtagService, UrlScraperService $scraper): void
    {
        $this->validate();

        $data = $this->data;

        $selectedProduct = !empty($data['product_id']) ? Product::find($data['product_id']) : null;

        if (empty($data['product_id']) && empty($data['product_url']) && empty($data['custom_name']) && empty($data['custom_description'])) {
            Notification::make()->title('Please select a Product or provide URL / Name / Description')->danger()->send();
            return;
        }

        $this->debugLog = null;

        try {
            $scrapedTitle = '';
            $scrapedDesc = '';
            $category = '';

            if ($selectedProduct) {
                // Kataloğdan seçilmiş ürün varsa URL scrape'e hiç gerek yok - Shopify
                // senkronundan gelen isim/açıklama/kategori direkt kullanılıyor.
                $scrapedTitle = $selectedProduct->name;
                $scrapedDesc = $selectedProduct->description ?? '';
                $category = $selectedProduct->category ?? '';
                $this->debugLog = 'Katalogdan seçildi: '.mb_substr($scrapedTitle, 0, 50);
            } elseif (!empty($data['product_url'])) {
                $scraped = $scraper->scrape($data['product_url']);
                $scrapedTitle = $scraped['title'];
                $scrapedDesc = $scraped['description'];
                $this->debugLog = 'Scraped: '.mb_substr($scrapedTitle, 0, 50);
            }

            $finalName = $data['custom_name'] ?: $scrapedTitle ?: 'Product';
            $finalDesc = $data['custom_description'] ?: $scrapedDesc ?: 'Natural cosmetic product';

            if (!empty($data['custom_description']) && !empty($scrapedDesc)) {
                $finalDesc = $data['custom_description'].' | Web info: '.$scrapedDesc;
            }

            $this->generated = $aiService->generateFromText([
                'name' => $finalName,
                'description' => $finalDesc,
                'ingredients' => '',
                'category' => $category,
                'url' => $selectedProduct ? ($selectedProduct->shopify_url ?? null) : ($data['product_url'] ?: null),
                'platform' => $data['platform'],
                'tone' => $data['tone'],
                'lang' => $data['lang'],
                'length' => $data['length'],
            ]);

            // Cache: regenerateHashtags() ve persistDraft() aynı nihai isim/açıklamayı
            // kullansın diye. ÖNEMLİ: burada $finalName/$finalDesc (ham/scrape edilmiş,
            // örn. Türkçe) DEĞİL, AiContentService'in caption için gerçekten kullandığı
            // ÇEVRİLMİŞ hâli kullanılıyor - aksi halde caption Almanca olsa da hashtag
            // üretimi hâlâ ham/karışık dille besleniyor ve alakasız sonuçlar geliyordu.
            $this->lastFinalName = $aiService->getLastTranslatedName() ?: $finalName;
            $this->lastFinalDesc = $aiService->getLastTranslatedDescription() ?: $finalDesc;

            // Gemini başarısız olup fallback'e düştüyse, hata sadece debug log'a gider - caption'a ASLA karışmaz.
            if ($aiService->getLastError()) {
                $this->debugLog .= ' | Gemini fallback: '.$aiService->getLastError();
            }

            $this->suggestedHashtags = $hashtagService->suggest(
                $this->lastFinalName,
                $this->lastFinalDesc,
                '',
                '',
                $this->generated ?? '',
                $data['lang']
            );

            if ($hashtagService->getLastError()) {
                $this->debugLog .= ' | Hashtag fallback: '.$hashtagService->getLastError();
            }

            Notification::make()->title('Content generated!')->success()->send();

        } catch (\Throwable $e) {
            // Kullanıcıya ham hata mesajı göstermek yerine dostane bir mesaj veriyoruz;
            // teknik detay sadece log'a ve debugLog'a gidiyor.
            Log::error('AiWriter generate failed: '.$e->getMessage(), ['exception' => $e]);
            $this->debugLog .= ' | ERROR: '.$e->getMessage();
            $this->generated = null;
            Notification::make()->title('Caption oluşturulamadı, lütfen tekrar deneyin.')->danger()->send();
        }
    }

    /**
     * Caption'a dokunmadan sadece hashtag'leri yeniden üretir.
     * generate() sırasında cache'lenen isim/açıklama kullanılır; URL varsa
     * tekrar scrape edilmez (gereksiz network çağrısı ve hata riski önlenir).
     */
    public function regenerateHashtags(HashtagService $hashtagService): void
    {
        if (!$this->generated) {
            Notification::make()->title('Önce bir caption oluşturun')->warning()->send();
            return;
        }

        $data = $this->data;

        // Cache boşsa (örn. sayfa state'i farklı şekilde restore edildiyse) form verisine düş.
        $finalName = $this->lastFinalName ?: ($data['custom_name'] ?: 'Product');
        $finalDesc = $this->lastFinalDesc ?: ($data['custom_description'] ?: 'Natural cosmetic product');

        try {
            $this->suggestedHashtags = $hashtagService->suggest(
                $finalName,
                $finalDesc,
                '',
                '',
                $this->generated,
                $data['lang'],
                // Önceki liste açıkça "bunları tekrar etme" olarak veriliyor - yoksa
                // aynı prompt Gemini'den neredeyse aynı sonucu getiriyor, "yenilenmiyor"
                // izlenimi veriyordu.
                $this->suggestedHashtags
            );

            if ($hashtagService->getLastError()) {
                $this->debugLog .= ' | Hashtag fallback: '.$hashtagService->getLastError();
            }

            Notification::make()->title('Hashtag\'ler yenilendi!')->success()->send();

        } catch (\Throwable $e) {
            Log::error('AiWriter regenerateHashtags failed: '.$e->getMessage(), ['exception' => $e]);
            $this->debugLog .= ' | ERROR: '.$e->getMessage();
            Notification::make()->title('Hashtag yenilenemedi, lütfen tekrar deneyin.')->danger()->send();
        }
    }

    public function saveAsDraft(): void
    {
        $this->persistDraft();
    }

    private function persistDraft(): void
    {
        if (!$this->generated) {
            Notification::make()->title('Nothing to save')->warning()->send();
            return;
        }

        $data = $this->data;

        try {
            $item = ContentItem::create([
                'user_id' => auth()->id(),
                // Artık gerçek seçim var - kullanıcı katalogdan ürün seçmediyse
                // (ör. sadece URL/manuel isim girdiyse) product_id nullable olduğu
                // için NULL kalıyor, eskisi gibi sahte bir "1" placeholder'ına
                // zorlanmıyor.
                'product_id' => $data['product_id'] ?? null,
                'platform' => $data['platform'],
                'status' => ContentStatus::Draft->value,
                'title' => $data['custom_name'] ?: (Product::find($data['product_id'] ?? null)?->name) ?: 'AI Generated',
                'body' => $this->generated."\n\n".implode(' ', $this->suggestedHashtags),
                // AiWriter'da yüklenen medya - ContentItem'ın kendi 'media' kolonu zaten
                // vardı, hiç kullanılmıyordu. EditContentItem'daki yayınlama butonu artık
                // önce buraya bakacak, yoksa ürün görseline düşecek.
                'media' => $data['media'] ?? null,
                'scheduled_at' => now()->addDay(),
                // EditContentItem::afterSave() -> AiLearningService::logEdit() ileride bu alanları
                // okuyacak (product_url, custom_description, tone, lang) - burada doldurulmazsa
                // düzenleme sırasında "öğrenme" kaydı eksik/boş bağlamla oluşur.
                'meta' => [
                    'product_url' => $data['product_url'] ?? null,
                    // Ham custom_description yerine nihai (scrape ile birleşmiş) açıklama saklanıyor;
                    // AiLearningService::logEdit() gerçek üretim bağlamını görsün diye.
                    'custom_description' => $this->lastFinalDesc ?: ($data['custom_description'] ?? ''),
                    'tone' => $data['tone'] ?? null,
                    'lang' => $data['lang'] ?? null,
                    'format' => $data['format'] ?? 'post',
                    'hashtags' => $this->suggestedHashtags,
                ],
            ]);

            app(AiLearningService::class)->logGeneration(
                auth()->id(),
                $data['custom_name'] ?? $data['product_url'] ?? null,
                $data['product_url'] ?? null,
                $this->lastFinalDesc ?: ($data['custom_description'] ?? ''),
                $this->generated,
                $data['platform'],
                $data['tone'] ?? null,
                $data['lang'] ?? null,
                ['hashtags' => $this->suggestedHashtags, 'content_item_id' => $item->id]
            );

            Notification::make()
                ->title('Saved as draft')
                ->body("ID: {$item->id} - Content Items listesinde")
                ->success()
                ->send();

            $this->redirect("/admin/contents/content-items/{$item->id}/edit");

        } catch (\Throwable $e) {
            Log::error('AiWriter saveAsDraft failed: '.$e->getMessage(), ['exception' => $e]);
            Notification::make()->title('Kaydetme başarısız, lütfen tekrar deneyin.')->danger()->send();
        }
    }
}
