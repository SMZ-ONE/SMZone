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
        return $table
            ->columns([
                ImageColumn::make('media')
                    ->label('Media')
                    ->circular()
                    ->stacked()
                    ->limit(3),

                TextColumn::make('title')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->limit(30)
                    ->placeholder('Untitled'),

                TextColumn::make('socialAccount.username')
                    ->label('Account')
                    ->prefix('@')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('socialAccount.platform')
                    ->label('Platform')
                    ->badge()
                    ->color(fn(?string $state): string => match($state){
                        'instagram' => 'danger',
                        'facebook' => 'info',
                        'tiktok' => 'gray',
                        default => 'gray'
                    }),

                TextColumn::make('product.name')
                    ->label('Product')
                    ->searchable()
                    ->badge()
                    ->color('warning')
                    ->placeholder('No product'),

                TextColumn::make('status')
                    ->badge()
                    ->icon(fn(string $state): string => match($state){
                        'draft' => 'heroicon-o-pencil-square',
                        'scheduled' => 'heroicon-o-clock',
                        'published' => 'heroicon-o-check-circle',
                        'failed' => 'heroicon-o-x-circle',
                        default => 'heroicon-o-question-mark-circle'
                    })
                    ->color(fn(string $state): string => match($state){
                        'draft' => 'gray',
                        'scheduled' => 'warning',
                        'published' => 'success',
                        'failed' => 'danger',
                        default => 'gray'
                    }),

                TextColumn::make('scheduled_at')
                    ->label('Scheduled')
                    ->since()
                    ->sortable()
                    ->placeholder('Not scheduled')
                    ->icon('heroicon-o-calendar'),

                TextColumn::make('created_at')
                    ->since()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                \Filament\Tables\Filters\SelectFilter::make('status')
                    ->options(['draft'=>'Draft','scheduled'=>'Scheduled','published'=>'Published','failed'=>'Failed']),
                \Filament\Tables\Filters\SelectFilter::make('platform')
                    ->options(['instagram'=>'Instagram','facebook'=>'Facebook','tiktok'=>'TikTok'])
                    ->query(function($q, $data){
                        if(!empty($data['value'])){
                            $q->whereHas('socialAccount', fn($qq) => $qq->where('platform', $data['value']));
                        }
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
            ->emptyStateHeading('No content yet')
            ->emptyStateDescription('Plan your first post and schedule it across your social accounts.')
            ->emptyStateIcon('heroicon-o-calendar-days');
    }
}
