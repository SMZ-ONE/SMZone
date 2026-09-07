<?php
namespace App\Filament\Resources\Contents\Tables;

use App\Enums\ContentStatus;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
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

                // Doğrudan content_items.platform kolonu - AI Writer'dan gelen kayıtlarda
                // social_account_id boş olabileceği için socialAccount.platform'a güvenilmiyor.
                TextColumn::make('platform')
                    ->badge()
                    ->color(fn (?string $state): string => match ($state) {
                        'instagram' => 'danger',
                        'facebook' => 'info',
                        'tiktok' => 'gray',
                        default => 'gray',
                    }),

                TextColumn::make('socialAccount.username')
                    ->label('Account')
                    ->prefix('@')
                    ->searchable()
                    ->sortable()
                    ->placeholder('—')
                    ->toggleable(),

                TextColumn::make('product.name')
                    ->label('Product')
                    ->searchable()
                    ->badge()
                    ->color('warning')
                    ->placeholder('No product'),

                // status bir ContentStatus enum instance'ı olarak gelir (model cast'i sayesinde) -
                // match() strict karşılaştırma yaptığı için önce ->value'ya çevirmemiz gerekiyor,
                // yoksa hiçbir case eşleşmez ve her satır 'default' rengine düşer.
                TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state instanceof \BackedEnum ? $state->value : $state)
                    ->icon(fn ($state): string => match ($state instanceof \BackedEnum ? $state->value : $state) {
                        'draft' => 'heroicon-o-pencil-square',
                        'scheduled' => 'heroicon-o-clock',
                        'published' => 'heroicon-o-check-circle',
                        'archived' => 'heroicon-o-archive-box',
                        default => 'heroicon-o-question-mark-circle',
                    })
                    ->color(fn ($state): string => match ($state instanceof \BackedEnum ? $state->value : $state) {
                        'draft' => 'gray',
                        'scheduled' => 'warning',
                        'published' => 'success',
                        'archived' => 'danger',
                        default => 'gray',
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
                    ->options([
                        'draft' => 'Draft',
                        'scheduled' => 'Scheduled',
                        'published' => 'Published',
                        'archived' => 'Archived',
                    ]),
                // content_items.platform kolonuna göre doğrudan filtreler - socialAccount join'ine gerek yok.
                \Filament\Tables\Filters\SelectFilter::make('platform')
                    ->options([
                        'instagram' => 'Instagram',
                        'facebook' => 'Facebook',
                        'tiktok' => 'TikTok',
                    ]),
            ])
            ->recordActions([
                // Manuel "yayınlandı" işaretleme - gerçek Instagram/TikTok API push'u henüz yok,
                // o entegrasyon eklendiğinde bu action'ın içine API çağrısı da eklenecek.
                Action::make('publish')
                    ->label('Publish')
                    ->icon('heroicon-o-rocket-launch')
                    ->color('success')
                    ->visible(fn ($record) => $record->status !== ContentStatus::Published)
                    ->requiresConfirmation()
                    ->modalHeading('Bu içerik yayınlandı olarak işaretlensin mi?')
                    ->modalDescription('Not: Bu, sosyal medyaya otomatik gönderim yapmaz - sadece durumu ve yayın tarihini günceller. Gerçek API entegrasyonu backlog\'da.')
                    ->action(function ($record) {
                        $record->update([
                            'status' => ContentStatus::Published,
                            'published_at' => now(),
                        ]);

                        Notification::make()->title('İçerik yayınlandı olarak işaretlendi')->success()->send();
                    }),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->emptyStateHeading('No content yet')
            ->emptyStateDescription('Plan your first post and schedule it across your social accounts.')
            ->emptyStateIcon('heroicon-o-calendar-days');
    }
}
