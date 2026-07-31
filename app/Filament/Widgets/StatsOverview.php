<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        return [
            Stat::make('Connected Accounts', '0')
                ->description('No accounts connected'),

            Stat::make('Scheduled Posts', '0')
                ->description('Nothing scheduled'),

            Stat::make('Published Today', '0')
                ->description('No posts published'),

            Stat::make('AI Tasks', '0')
                ->description('Ready'),
        ];
    }
}