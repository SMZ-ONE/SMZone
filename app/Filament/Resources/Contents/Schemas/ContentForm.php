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
            TextInput::make('title')->required()->maxLength(255)->columnSpanFull(),
            Select::make('product_id')->relationship('product','name')->searchable()->preload(),
            Select::make('social_account_id')->relationship('socialAccount','username')->searchable()->preload(),
            Textarea::make('body')->rows(5)->columnSpanFull()->placeholder('Caption buraya...'),
            FileUpload::make('image')->image()->directory('contents'),
            Select::make('status')->options(['draft'=>'Draft','scheduled'=>'Scheduled','published'=>'Published'])->default('draft')->required(),
            DateTimePicker::make('scheduled_at'),
            Select::make('platforms')->multiple()->options(['instagram'=>'Instagram','facebook'=>'Facebook','tiktok'=>'TikTok']),
        ]);
    }
}
