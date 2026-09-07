<?php

namespace App\Filament\Resources\Contents;

use App\Filament\Resources\Contents\Schemas\ContentForm;
use App\Filament\Resources\Contents\Tables\ContentsTable;
use App\Models\ContentItem;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use UnitEnum;

class ContentItemResource extends Resource
{
    protected static ?string $model = ContentItem::class;
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-document-text';
    protected static string|UnitEnum|null $navigationGroup = 'SMZ ONE - Content';
    protected static ?string $navigationLabel = 'Contents';
    protected static ?string $modelLabel = 'Content';
    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return ContentForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ContentsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListContentItems::route('/'),
            'create' => Pages\CreateContentItem::route('/create'),
            'edit' => Pages\EditContentItem::route('/{record}/edit'),
        ];
    }
}
