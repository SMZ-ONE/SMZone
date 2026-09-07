<?php

namespace App\Filament\Resources\Contents\Pages;

use App\Filament\Resources\Contents\ContentItemResource;
use Filament\Resources\Pages\ListRecords;

class ListContentItems extends ListRecords
{
    protected static string $resource = ContentItemResource::class;
}
