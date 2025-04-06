<?php

namespace App\Http\Controllers\Api;

use App\Models\OfficeSpace;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\OfficeSpaceResource;
use Illuminate\Support\Facades\Cache;

class OfficeSpaceController extends Controller
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
        $json = $this->cacheJson('office_spaces_list', 60, function () {
            $offices = OfficeSpace::with([
                'city',
                'photos',
                'benefits'
            ])->get();

            return OfficeSpaceResource::collection($offices)->toArray(request());
        });

        return response()->json(json_decode($json, true));
    }

    public function show(OfficeSpace $officeSpace)
    {
        $json = $this->cacheJson('office_space_' . $officeSpace->id, 60, function () use ($officeSpace) {
            $officeSpace->load([
                'city',
                'photos',
                'benefits'
            ]);

            return OfficeSpaceResource::make($officeSpace)->toArray(request());
        });

        return response()->json(json_decode($json, true));
    }
}
