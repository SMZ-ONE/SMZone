<?php
namespace App\Filament\Resources\Contents\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ContentsTable
{
    public static function configure(Table $table): Table
    {
        return $table->columns([
            ImageColumn::make('image'),
            TextColumn::make('title')->searchable()->limit(30),
            TextColumn::make('product.name')->badge(),
            TextColumn::make('status')->badge()->color(fn($state)=> match($state){'draft'=>'gray','scheduled'=>'warning','published'=>'success',default=>'gray'}),
            TextColumn::make('scheduled_at')->dateTime()->sortable(),
        ])->filters([])
        ->recordActions([EditAction::make()])
        ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }
}
