<?php
namespace App\Filament\Resources\Products\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            FileUpload::make('image')
                ->label('Product Image')
                ->image()
                ->directory('products')
                ->disk('public')
                ->columnSpanFull(),

            TextInput::make('name')
                ->required()
                ->maxLength(255)
                ->placeholder('e.g. Argan Hair Oil'),

            TextInput::make('sku')
                ->label('SKU')
                ->unique(ignoreRecord: true)
                ->prefixIcon('heroicon-o-tag')
                ->placeholder('SMZ-001'),

            TextInput::make('price')
                ->numeric()
                ->prefix('€')
                ->required()
                ->minValue(0)
                ->step(0.01),

            TextInput::make('stock')
                ->label('Stock Qty')
                ->numeric()
                ->default(0)
                ->minValue(0),

            Textarea::make('description')
                ->rows(3)
                ->columnSpanFull()
                ->placeholder('Short product description...'),

            TagsInput::make('tags')
                ->placeholder('Add tag')
                ->columnSpanFull(),

            Toggle::make('is_active')
                ->label('Active / Published')
                ->default(true)
                ->inline(false),
        ]);
    }
}
