<?php

namespace App\Filament\Pages;

use BackedEnum;
use Filament\Pages\Page;
use UnitEnum;

class AiCenter extends Page
{
    protected string $view = 'filament.pages.ai-center';
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-sparkles';
    protected static string|UnitEnum|null $navigationGroup = 'SMZ ONE - Brand';
    protected static ?string $navigationLabel = 'Brand Center';
    protected static ?int $navigationSort = 0;
    protected static ?string $title = 'Brand Center';
}
