<?php
namespace App\Filament\Resources\Contents\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class ContentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('social_account_id')
                ->relationship('socialAccount', 'username')
                ->label('Social Account')
                ->prefixIcon('heroicon-o-at-symbol')
                ->searchable()
                ->preload()
                ->required(),

            Select::make('product_id')
                ->relationship('product', 'name')
                ->label('Linked Product')
                ->prefixIcon('heroicon-o-shopping-bag')
                ->searchable()
                ->preload()
                ->nullable(),

            Select::make('status')
                ->options([
                    'draft' => 'Draft',
                    'scheduled' => 'Scheduled',
                    'published' => 'Published',
                    'failed' => 'Failed',
                ])
                ->default('draft')
                ->required()
                ->native(false),

            Select::make('platform')
                ->options([
                    'instagram' => 'Instagram',
                    'facebook' => 'Facebook',
                    'tiktok' => 'TikTok',
                ])
                ->native(false),

            TextInput::make('title')
                ->maxLength(255)
                ->placeholder('e.g. New Year Campaign')
                ->columnSpanFull(),

            Textarea::make('caption')
                ->rows(4)
                ->columnSpanFull()
                ->placeholder('Write your caption with #hashtags...'),

            FileUpload::make('media')
                ->label('Media')
                ->image()
                ->multiple()
                ->directory('contents')
                ->disk('public')
                ->columnSpanFull(),

            DateTimePicker::make('scheduled_at')
                ->label('Schedule For')
                ->native(false)
                ->seconds(false),

            DateTimePicker::make('published_at')
                ->label('Published At')
                ->disabled()
                ->dehydrated(false)
                ->visibleOn('edit'),
        ]);
    }
}
