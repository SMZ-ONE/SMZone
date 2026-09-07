<?php

namespace App\Filament\Pages;

use App\Models\BrandProfile;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use BackedEnum;
use UnitEnum;

class BrandStudio extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-paint-brush';
    protected static string|UnitEnum|null $navigationGroup = 'SMZ ONE - Brand';
    protected static ?string $navigationLabel = 'Brand Studio';
    protected static ?string $title = 'Brand Studio';
    protected static ?int $navigationSort = 1;
    protected string $view = 'filament.pages.brand-studio';

    public ?array $data = [];

    public function mount(): void
    {
        $profile = BrandProfile::activeForUser();

        $this->form->fill([
            'name' => $profile?->name ?? 'byCOSMETIQ',
            'website_url' => $profile?->website_url ?? '',
            'tone_default' => $profile?->tone_default ?? 'professional',
            'lang_default' => $profile?->lang_default ?? 'de',
            'brand_voice' => $profile?->brand_voice ?? 'Natürlich, ehrlich, ohne Greenwashing. Expertin für natürliche Hautpflege. Kein übertriebener Marketing-Sprech.',
            'brand_story' => $profile?->brand_story ?? '',
            'forbidden_words' => $profile?->forbidden_words ?? ['SMZ', 'SMZ ONE', 'smzone.eu'],
            'preferred_hashtags' => $profile?->preferred_hashtags ?? ['#bycosmetiq', '#bycosmetiqfamily'],
            'brand_keywords' => $profile?->brand_keywords ?? ['Argan', 'Rose', 'Aloe Vera', 'Vegan', 'Bio', 'Naturkosmetik'],
            'extra_instructions' => $profile?->extra_instructions ?? 'Max 1-2 Emojis, authentisch. Kein übertriebener Verkaufston.',
            'is_active' => $profile?->is_active ?? true,
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')
                ->label('Brand Name')
                ->required()
                ->maxLength(100)
                ->columnSpanFull(),

            TextInput::make('website_url')
                ->label('Website / Shop URL')
                ->placeholder('z.B. https://bycosmetiq.com')
                ->url()
                ->nullable()
                ->columnSpanFull()
                ->helperText('AI wird NUR diese Domain als CTA-Link verwenden - nie eine andere erfinden oder eine falsche Domain aus gescrapten Fremd-Seiten übernehmen.'),

            Select::make('tone_default')
                ->label('Default Tone')
                ->options([
                    'professional' => 'Professional - Expert & trustworthy',
                    'friendly' => 'Friendly - Warm & conversational',
                    'playful' => 'Playful - Fun & energetic',
                    'luxury' => 'Luxury - Premium & elegant',
                ])
                ->native(false)
                ->required(),

            Select::make('lang_default')
                ->label('Default Language')
                ->options(['de' => 'Deutsch', 'en' => 'English'])
                ->native(false)
                ->required(),

            Textarea::make('brand_voice')
                ->label('Brand Voice - Wie spricht deine Marke?')
                ->placeholder('z.B. Natürlich, ehrlich, direkt, ohne Marketing-Blabla...')
                ->rows(3)
                ->columnSpanFull()
                ->helperText('AI wird jeden Caption in diesem Stil schreiben.'),

            Textarea::make('brand_story')
                ->label('Brand Story (Optional)')
                ->placeholder('z.B. byCOSMETIQ steht für...')
                ->rows(3)
                ->columnSpanFull()
                ->helperText('Hintergrundwissen für AI - wird nicht 1:1 kopiert, aber Ton und Kontext beeinflusst.'),

            TagsInput::make('forbidden_words')
                ->label('Forbidden Words - AI darf diese NIE verwenden')
                ->placeholder('Wort eingeben + Enter')
                ->columnSpanFull()
                ->helperText('Interne Tool-/Systemnamen (z.B. dein internes Verwaltungssystem) gehören hier rein - Kunden sollen sie nie sehen.'),

            TagsInput::make('preferred_hashtags')
                ->label('Preferred Hashtags - Immer dabei')
                ->placeholder('#hashtag + Enter')
                ->columnSpanFull(),

            TagsInput::make('brand_keywords')
                ->label('Brand Keywords - Marken-DNA')
                ->placeholder('Keyword + Enter')
                ->columnSpanFull(),

            Textarea::make('extra_instructions')
                ->label('Extra AI Instructions')
                ->rows(2)
                ->columnSpanFull()
                ->placeholder('z.B. Max 1 Emoji, immer mit Call-to-Action...'),

            Toggle::make('is_active')
                ->label('Active Profile')
                ->default(true),
        ])->statePath('data');
    }

    public function save(): void
    {
        $profile = BrandProfile::activeForUser() ?? new BrandProfile();
        $profile->fill($this->data);
        $profile->user_id = auth()->id();
        $profile->save();

        Notification::make()->title('Brand Studio gespeichert!')->success()->send();
    }
}
