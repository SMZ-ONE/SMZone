<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use BackedEnum;
use UnitEnum;

class DeepAnalytics extends Page
{
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-chart-bar-square';
    protected static string|UnitEnum|null $navigationGroup = 'SMZ ONE - Analytics';
    protected static ?string $navigationLabel = 'Deep Analytics';
    protected static ?string $title = 'Deep Analytics';
    protected static ?int $navigationSort = 1;
    protected string $view = 'filament.pages.deep-analytics';
}
