<?php

namespace App\Filament\Resources\SocialAccounts\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class SocialAccountForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('user_id')
                ->relationship('user', 'name')
                ->default(auth()->id())
                ->required(),
            Select::make('provider')
                ->options([
                    'facebook' => 'Facebook',
                    'instagram' => 'Instagram',
                ])
                ->required(),
            TextInput::make('account_name')->label('Page Name')->required(),
            TextInput::make('username'),
            TextInput::make('account_id')->label('Account ID'),
            Toggle::make('is_active')->default(true),
        ]);
    }
}