<?php

namespace App\Http\Controllers\Api;

use App\Models\City;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;
use App\Http\Resources\Api\CityResource;

class CityController extends Controller
{
    public function index()
    {
        return Cache::remember('cities_list', now()->addHours(1), function () {
            $cities = City::with([
                'officeSpaces' => function ($query) {
                    $query->with(['city', 'photos']);
                }
            ])->get();

            return CityResource::collection($cities);
        });
    }

    public function show(City $city)
    {
        return Cache::remember('city_' . $city->id, now()->addHours(1), function () use ($city) {
            $city->load([
                'officeSpaces.city',
                'officeSpaces.photos'
            ]);

            return CityResource::make($city);
        });
    }
}
