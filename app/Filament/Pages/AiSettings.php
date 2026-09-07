<?php

namespace App\Filament\Pages;

use App\Models\Setting;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use BackedEnum;
use UnitEnum;

class AiSettings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-cog-6-tooth';
    protected static string|UnitEnum|null $navigationGroup = 'SMZ ONE - Settings';
    protected static ?string $title = 'AI Keys & Security';
    protected string $view = 'filament.pages.ai-settings';
    protected static ?int $navigationSort = 99;

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            'gemini_api_key' => Setting::get('gemini_api_key'),
            'openai_api_key' => Setting::get('openai_api_key'),
            'shopify_store_domain' => Setting::get('shopify_store_domain'),
            'shopify_access_token' => Setting::get('shopify_access_token'),
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('gemini_api_key')
                    ->label('Gemini API Key (Google AI Studio)')
                    ->password()
                    ->revealable()
                    ->helperText('Google AI Studio > Create API Key. DB de sifreli saklanir, .env de degil.')
                    ->suffixAction(
                        Action::make('deleteGeminiKey')
                            ->label('Sil')
                            ->icon('heroicon-o-trash')
                            ->color('danger')
                            ->visible(fn () => filled(Setting::get('gemini_api_key')))
                            ->requiresConfirmation()
                            ->modalHeading('Gemini API Key silinsin mi?')
                            ->modalDescription('Bu key veritabanından tamamen silinecek. Geri alınamaz.')
                            ->action(function () {
                                Setting::forget('gemini_api_key');
                                $this->form->fill([
                                    'gemini_api_key' => null,
                                    'openai_api_key' => Setting::get('openai_api_key'),
                                    'shopify_store_domain' => Setting::get('shopify_store_domain'),
                                    'shopify_access_token' => Setting::get('shopify_access_token'),
                                ]);
                                Notification::make()->title('Gemini API key silindi')->success()->send();
                            })
                    )
                    ->columnSpanFull(),

                TextInput::make('openai_api_key')
                    ->label('OpenAI API Key (Opsiyonel)')
                    ->password()
                    ->revealable()
                    ->helperText('Ileride kullanmak istersen.')
                    ->suffixAction(
                        Action::make('deleteOpenAiKey')
                            ->label('Sil')
                            ->icon('heroicon-o-trash')
                            ->color('danger')
                            ->visible(fn () => filled(Setting::get('openai_api_key')))
                            ->requiresConfirmation()
                            ->modalHeading('OpenAI API Key silinsin mi?')
                            ->modalDescription('Bu key veritabanından tamamen silinecek. Geri alınamaz.')
                            ->action(function () {
                                Setting::forget('openai_api_key');
                                $this->form->fill([
                                    'gemini_api_key' => Setting::get('gemini_api_key'),
                                    'openai_api_key' => null,
                                    'shopify_store_domain' => Setting::get('shopify_store_domain'),
                                    'shopify_access_token' => Setting::get('shopify_access_token'),
                                ]);
                                Notification::make()->title('OpenAI API key silindi')->success()->send();
                            })
                    )
                    ->columnSpanFull(),

                TextInput::make('shopify_store_domain')
                    ->label('Shopify Mağaza Domain')
                    ->placeholder('ör. pp0000-rk.myshopify.com')
                    ->helperText('Görünen özel domain (bycosmetiq.com) DEĞİL - Shopify Admin\'de "Develop apps" sayfasında gördüğün .myshopify.com adresi.')
                    ->suffixAction(
                        Action::make('deleteShopifyDomain')
                            ->label('Sil')
                            ->icon('heroicon-o-trash')
                            ->color('danger')
                            ->visible(fn () => filled(Setting::get('shopify_store_domain')))
                            ->requiresConfirmation()
                            ->modalHeading('Shopify domain silinsin mi?')
                            ->modalDescription('Bu bilgi veritabanından tamamen silinecek. Geri alınamaz.')
                            ->action(function () {
                                Setting::forget('shopify_store_domain');
                                $this->form->fill([
                                    'gemini_api_key' => Setting::get('gemini_api_key'),
                                    'openai_api_key' => Setting::get('openai_api_key'),
                                    'shopify_store_domain' => null,
                                    'shopify_access_token' => Setting::get('shopify_access_token'),
                                ]);
                                Notification::make()->title('Shopify domain silindi')->success()->send();
                            })
                    )
                    ->columnSpanFull(),

                TextInput::make('shopify_access_token')
                    ->label('Shopify Admin API Access Token')
                    ->password()
                    ->revealable()
                    ->placeholder('shpat_...')
                    ->helperText('Shopify Admin > Settings > Apps and sales channels > Develop apps > [App] > API credentials. DB de sifreli saklanir.')
                    ->suffixAction(
                        Action::make('deleteShopifyToken')
                            ->label('Sil')
                            ->icon('heroicon-o-trash')
                            ->color('danger')
                            ->visible(fn () => filled(Setting::get('shopify_access_token')))
                            ->requiresConfirmation()
                            ->modalHeading('Shopify Access Token silinsin mi?')
                            ->modalDescription('Bu key veritabanından tamamen silinecek. Geri alınamaz.')
                            ->action(function () {
                                Setting::forget('shopify_access_token');
                                $this->form->fill([
                                    'gemini_api_key' => Setting::get('gemini_api_key'),
                                    'openai_api_key' => Setting::get('openai_api_key'),
                                    'shopify_store_domain' => Setting::get('shopify_store_domain'),
                                    'shopify_access_token' => null,
                                ]);
                                Notification::make()->title('Shopify Access Token silindi')->success()->send();
                            })
                    )
                    ->columnSpanFull(),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $state = $this->form->getState();

        if (!empty($state['gemini_api_key'])) {
            Setting::set('gemini_api_key', $state['gemini_api_key']);
        }
        if (!empty($state['openai_api_key'])) {
            Setting::set('openai_api_key', $state['openai_api_key']);
        }
        if (!empty($state['shopify_store_domain'])) {
            Setting::set('shopify_store_domain', $state['shopify_store_domain']);
        }
        if (!empty($state['shopify_access_token'])) {
            Setting::set('shopify_access_token', $state['shopify_access_token']);
        }

        Notification::make()
            ->title('Keyler sifreli olarak kaydedildi!')
            ->body('DB de AES-256 encrypted. Gite gitmez.')
            ->success()
            ->send();

        $this->form->fill([
            'gemini_api_key' => Setting::get('gemini_api_key'),
            'openai_api_key' => Setting::get('openai_api_key'),
            'shopify_store_domain' => Setting::get('shopify_store_domain'),
            'shopify_access_token' => Setting::get('shopify_access_token'),
        ]);
    }
}
