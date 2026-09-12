<?php

namespace App\Filament\Resources\SocialAccounts\Pages;

use App\Filament\Resources\SocialAccounts\SocialAccountResource;
use App\Models\SocialAccount;
use App\Services\MetaService;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;

class ListSocialAccounts extends ListRecords
{
    protected static string $resource = SocialAccountResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('discoverFromMeta')
                ->label('Discover from Meta')
                ->icon('heroicon-o-magnifying-glass')
                ->color('gray')
                ->form([
                    TextInput::make('token')
                        ->label('User Access Token')
                        ->placeholder('EAAxxxxx...')
                        ->required()
                        ->helperText('pages_show_list ve instagram_basic izinleriyle alınmış olmalı'),
                ])
                ->action(function (array $data) {
                    $meta = app(MetaService::class);
                    $pages = $meta->discoverAccounts($data['token']);

                    if (empty($pages)) {
                        Notification::make()
                            ->title('Hiçbir hesap bulunamadı')
                            ->body($meta->getLastError())
                            ->danger()
                            ->send();
                        return;
                    }

                    $saved = 0;

                    foreach ($pages as $page) {
                        SocialAccount::updateOrCreate(
                            ['platform' => 'facebook', 'provider_id' => $page['page_id']],
                            [
                                'username' => $page['page_name'],
                                'access_token' => $page['page_token'],
                                'is_connected' => true,
                                'last_synced_at' => now(),
                            ]
                        );
                        $saved++;

                        if ($page['instagram']) {
                            SocialAccount::updateOrCreate(
                                ['platform' => 'instagram', 'provider_id' => $page['instagram']['id']],
                                [
                                    'username' => $page['instagram']['username'],
                                    'access_token' => $page['page_token'],
                                    'avatar' => $page['instagram']['avatar'],
                                    'is_connected' => true,
                                    'last_synced_at' => now(),
                                ]
                            );
                            $saved++;
                        }
                    }

                    Notification::make()
                        ->title("{$saved} hesap kaydedildi/güncellendi")
                        ->body('İstemediklerini aşağıdaki listeden düzenleyip Connected\'ı kapatabilir veya silebilirsin.')
                        ->success()
                        ->send();
                }),
            CreateAction::make(),
        ];
    }
}
