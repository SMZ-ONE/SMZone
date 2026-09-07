<?php
namespace App\Filament\Resources\Products\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ProductsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('image')
                    ->circular()
                    ->defaultImageUrl(fn() => 'https://ui-avatars.com/api/?name=P&background=f59e0b&color=fff'),

                TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->description(fn($record) => 'SKU: ' . $record->sku)
                    ->limit(30),

                TextColumn::make('price')
                    ->money('EUR')
                    ->sortable()
                    ->weight('bold')
                    ->color('success'),

                TextColumn::make('sku')
                    ->label('SKU')
                    ->badge()
                    ->color('gray')
                    ->searchable(),

                TextColumn::make('stock')
                    ->sortable()
                    ->badge()
                    ->color(fn($state) => $state > 10 ? 'success' : ($state > 0 ? 'warning' : 'danger')),

                TextColumn::make('tags')
                    ->badge()
                    ->separator(',')
                    ->color('info'),

                IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean(),
            ])
            ->filters([
                \Filament\Tables\Filters\TernaryFilter::make('is_active')->label('Active'),
                \Filament\Tables\Filters\Filter::make('in_stock')->query(fn($q) => $q->where('stock','>',0))->label('In Stock'),
            ])
            ->recordActions([ EditAction::make() ])
            ->toolbarActions([ BulkActionGroup::make([ DeleteBulkAction::make() ]) ])
            ->emptyStateHeading('No products')
            ->emptyStateDescription('Add your first natural cosmetic product.')
            ->emptyStateIcon('heroicon-o-shopping-bag');
    }
}
