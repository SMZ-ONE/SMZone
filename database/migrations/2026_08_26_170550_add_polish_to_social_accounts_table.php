<?php
namespace App\Filament\Resources\SocialAccounts\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class SocialAccountsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('avatar')
                    ->circular()
                    ->defaultImageUrl(fn($record) => 'https://ui-avatars.com/api/?name='.urlencode($record->username ?? 'SA').'&background=random'),

                TextColumn::make('platform')
                    ->badge()
                    ->icon(fn(string $state): string => match($state){
                        'instagram' => 'heroicon-o-camera',
                        'facebook' => 'heroicon-o-globe-alt',
                        'tiktok' => 'heroicon-o-musical-note',
                        default => 'heroicon-o-link'
                    })
                    ->color(fn(string $state): string => match($state){
                        'instagram' => 'danger',
                        'facebook' => 'info',
                        'tiktok' => 'gray',
                        default => 'gray'
                    })
                    ->searchable()
                    ->sortable(),

                TextColumn::make('username')
                    ->prefix('@')
                    ->searchable()
                    ->weight('bold')
                    ->sortable(),

                IconColumn::make('is_connected')
                    ->label('Status')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),

                TextColumn::make('last_synced_at')
                    ->label('Last Sync')
                    ->since()
                    ->sortable()
                    ->placeholder('Never')
                    ->icon('heroicon-o-clock'),

                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                \Filament\Tables\Filters\SelectFilter::make('platform')
                    ->options(['instagram'=>'Instagram','facebook'=>'Facebook','tiktok'=>'TikTok']),
                \Filament\Tables\Filters\TernaryFilter::make('is_connected')->label('Connected'),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateHeading('No social accounts yet')
            ->emptyStateDescription('Connect your first Instagram, Facebook or TikTok account to start publishing.')
            ->emptyStateIcon('heroicon-o-share');
    }
}
