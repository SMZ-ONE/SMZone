<?php

namespace App\Filament\Widgets;

use App\Enums\ContentStatus;
use App\Models\ContentItem;
use App\Models\Product;
use App\Models\SocialAccount;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $accounts = SocialAccount::count();
        $scheduled = ContentItem::where('status', ContentStatus::Scheduled)->count();
        $publishedToday = ContentItem::where('status', ContentStatus::Published)->whereDate('published_at', today())->count();
        $products = Product::count();

        return [
            Stat::make('Connected Accounts', $accounts)
                ->description($accounts ? 'Active across platforms' : 'No accounts connected')
                ->descriptionIcon($accounts ? 'heroicon-m-check-circle' : 'heroicon-m-x-circle')
                ->color($accounts ? 'success' : 'danger')
                ->chart($accounts ? [2,3,5,8,12] : [0,0,0]),

            Stat::make('Scheduled Posts', $scheduled)
                ->description($scheduled ? 'Ready to publish' : 'Nothing scheduled')
                ->descriptionIcon('heroicon-m-clock')
                ->color($scheduled ? 'warning' : 'gray'),

            Stat::make('Published Today', $publishedToday)
                ->description($publishedToday ? 'Today' : 'No posts today')
                ->descriptionIcon('heroicon-m-rocket-launch')
                ->color($publishedToday ? 'success' : 'gray'),

            Stat::make('Products', $products)
                ->description('In catalog')
                ->descriptionIcon('heroicon-m-shopping-bag')
                ->color('warning'),
        ];
    }
}
