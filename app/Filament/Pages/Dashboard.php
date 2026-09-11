<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\StatsOverview;
use App\Filament\Widgets\TodayScheduleBanner;
use App\Filament\Widgets\QuickActions;
use App\Filament\Widgets\RecentPosts;
use App\Filament\Widgets\PlatformConnections;
use App\Filament\Widgets\ComingSoonMetrics;
use App\Filament\Widgets\ContentHeatmap;
use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    protected static ?string $navigationLabel = 'Dashboard';

    public function getWidgets(): array
    {
        return [
            TodayScheduleBanner::class,
            StatsOverview::class,
            QuickActions::class,
            RecentPosts::class,
            PlatformConnections::class,
            ComingSoonMetrics::class,
            ContentHeatmap::class,
        ];
    }
}
