<?php

namespace App\Filament\Resources\DataStudentResource\Pages;

use App\Filament\Resources\DataStudentResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateDataStudent extends CreateRecord
{
    protected static string $resource = DataStudentResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
