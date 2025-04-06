<?php

namespace App\Http\Controllers\Api;

use App\Models\City;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;
use App\Http\Resources\Api\CityResource;

class CityController extends Controller
{
    protected function getJsonCacheKey($key)
    {
        return 'json_' . $key;
    }

    protected function cacheJson($key, $minutes, $callback)
    {
        $jsonKey = $this->getJsonCacheKey($key);

        return Cache::remember($jsonKey, $minutes, function () use ($callback) {
            return json_encode($callback());
        });
    }

    public function index()
    {
        $json = $this->cacheJson('cities_list', 60, function () {
            $cities = City::withCount('officeSpaces')
                ->with([
                    'officeSpaces' => function ($query) {
                        $query->with(['city', 'photos']);
                    }
                ])
                ->get();

            return CityResource::collection($cities)->toArray(request());
        });

        return response()->json(json_decode($json, true));
    }

    public function show(City $city)
    {
        $json = $this->cacheJson('city_' . $city->id, 60, function () use ($city) {
            $city->load([
                'officeSpaces.city',
                'officeSpaces.photos'
            ]);
            $city->loadCount('officeSpaces');

            return CityResource::make($city)->toArray(request());
        });

        return response()->json(json_decode($json, true));
    }
}
