<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\ContentCalendarWidget;
use Filament\Pages\Page;
use BackedEnum;
use UnitEnum;

class ContentCalendarPage extends Page
{
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-calendar-days';
    // AiWriter 'SMZ ONE - AI' altındaydı, ContentItemResource 'SMZ ONE - Content'
    // altında - takvim de içerikle ilgili olduğu için aynı gruba konuldu.
    protected static string|UnitEnum|null $navigationGroup = 'SMZ ONE - Content';
    protected static ?string $navigationLabel = 'Content Calendar';
    protected static ?string $title = 'Content Calendar';
    protected static ?int $navigationSort = 1;
    protected string $view = 'filament.pages.content-calendar-page';
}
