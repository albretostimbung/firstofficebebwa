<?php

namespace App\Filament\Resources\CityResource\Pages;

use App\Filament\Resources\CityResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use App\Models\City;
use Illuminate\Database\Eloquent\Model;

class EditCity extends EditRecord
{
    protected static string $resource = CityResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()->before(function (City $city) {
                CityResource::clearCache($city);
            }),
        ];
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        $city = parent::handleRecordUpdate($record, $data);
        CityResource::clearCache($city);
        return $city;
    }
}
