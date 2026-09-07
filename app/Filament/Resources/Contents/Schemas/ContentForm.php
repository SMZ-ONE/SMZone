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
                ->nullable(),

            Select::make('product_id')
                ->relationship('product', 'name')
                ->label('Linked Product')
                ->prefixIcon('heroicon-o-shopping-bag')
                ->searchable()
                ->preload()
                ->nullable(),

            // ContentStatus enum'daki gerçek değerlerle uyumlu (draft/scheduled/published/archived).
            // Not: eski versiyonda 'failed' vardı ama enum'da böyle bir case yok.
            Select::make('status')
                ->options([
                    'draft' => 'Draft',
                    'scheduled' => 'Scheduled',
                    'published' => 'Published',
                    'archived' => 'Archived',
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
                ->required()
                ->native(false),

            TextInput::make('title')
                ->maxLength(255)
                ->placeholder('e.g. New Year Campaign')
                ->columnSpanFull(),

            // ContentItem modelindeki gerçek kolon adı 'body' - 'caption' değil.
            Textarea::make('body')
                ->label('Caption + Hashtags')
                ->rows(6)
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
