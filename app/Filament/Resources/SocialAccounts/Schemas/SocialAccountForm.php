<?php
namespace App\Filament\Resources\SocialAccounts\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class SocialAccountForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('platform')
                ->options([
                    'instagram' => 'Instagram',
                    'facebook' => 'Facebook',
                    'tiktok' => 'TikTok',
                ])
                ->required()
                ->native(false)
                ->prefixIcon('heroicon-o-globe-alt'),

            TextInput::make('username')
                ->required()
                ->prefix('@')
                ->maxLength(255),

            TextInput::make('provider_id')
                ->label('Provider ID')
                ->placeholder('123456789')
                ->helperText('Platformdan gelen ID'),

            FileUpload::make('avatar')
                ->image()
                ->avatar()
                ->directory('avatars')
                ->disk('public'),

            TextInput::make('access_token')
                ->password()
                ->revealable()
                ->columnSpanFull()
                ->helperText('Token güvenli saklanır'),

            Toggle::make('is_connected')
                ->label('Connected')
                ->default(false)
                ->inline(false),
        ]);
    }
}
