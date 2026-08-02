<?php

namespace App\Filament\Pages;

use BackedEnum;
use Filament\Pages\Page;
use UnitEnum;

class AiCenter extends Page
{
    protected string $view = 'filament.pages.ai-center';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-sparkles';
    protected static string|UnitEnum|null $navigationGroup = 'SMZone';
    protected static ?string $navigationLabel = 'AI Center';
    protected static ?int $navigationSort = 4;
    protected static ?string $title = 'AI Center (Coming Soon)';
}
