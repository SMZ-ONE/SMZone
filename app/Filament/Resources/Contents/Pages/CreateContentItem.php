<?php

namespace App\Filament\Resources\Contents\Pages;

use App\Filament\Resources\Contents\ContentItemResource;
use Filament\Resources\Pages\CreateRecord;

class CreateContentItem extends CreateRecord
{
    protected static string $resource = ContentItemResource::class;
}
