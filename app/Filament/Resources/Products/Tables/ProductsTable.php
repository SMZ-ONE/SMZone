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
                    ->defaultImageUrl(fn($r) => 'https://ui-avatars.com/api/?name='.urlencode($r->name ?? 'P').'&background=random'),

                TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->description(fn($record) => $record->sku ? 'SKU: '.$record->sku : null),

                TextColumn::make('price')
                    ->money('EUR')
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('sku')
                    ->badge()
                    ->color('gray')
                    ->searchable()
                    ->toggleable(),

                TextColumn::make('stock')
                    ->label('Stock')
                    ->sortable()
                    ->badge()
                    ->color(fn($state) => $state > 10 ? 'success' : ($state > 0 ? 'warning' : 'danger')),

                TextColumn::make('tags')
                    ->badge()
                    ->separator(',')
                    ->color('info'),

                IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('gray'),

                TextColumn::make('created_at')
                    ->since()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                \Filament\Tables\Filters\TernaryFilter::make('is_active')->label('Active'),
                \Filament\Tables\Filters\SelectFilter::make('tags')->options(['natural'=>'natural','organic'=>'organic','bestseller'=>'bestseller'])->query(function($q, $data){
                    if(!empty($data['value'])){ $q->where('tags','like','%'.$data['value'].'%'); }
                    return $q;
                }),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateHeading('No products yet')
            ->emptyStateDescription('Create your first product to start building your catalog.')
            ->emptyStateIcon('heroicon-o-shopping-bag');
    }
}
