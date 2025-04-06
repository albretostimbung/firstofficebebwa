<?php

namespace App\Filament\Resources\CityResource\Pages;

use App\Filament\Resources\CityResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use App\Models\City;
use Illuminate\Database\Eloquent\Model;

class CreateCity extends CreateRecord
{
    protected static string $resource = CityResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        $city = parent::handleRecordCreation($data);
        CityResource::clearCache($city);
        return $city;
    }
}
