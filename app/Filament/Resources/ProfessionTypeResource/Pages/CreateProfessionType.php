<?php

declare(strict_types=1);

namespace App\Filament\Resources\ProfessionTypeResource\Pages;

use App\Filament\Resources\ProfessionTypeResource;
use Filament\Resources\Pages\CreateRecord;

class CreateProfessionType extends CreateRecord
{
    protected static string $resource = ProfessionTypeResource::class;
}
