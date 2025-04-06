<?php

namespace App\Http\Controllers\Api;

use App\Models\OfficeSpace;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\OfficeSpaceResource;
use Illuminate\Support\Facades\Cache;

class OfficeSpaceController extends Controller
{
    public function index()
    {
        return Cache::remember('office_spaces_list', now()->addHours(1), function () {
            $offices = OfficeSpace::with([
                'city',
                'photos',
                'benefits'
            ])->get();

            return OfficeSpaceResource::collection($offices);
        });
    }

    public function show(OfficeSpace $officeSpace)
    {
        return Cache::remember('office_space_' . $officeSpace->id, now()->addHours(1), function () use ($officeSpace) {
            $officeSpace->load(['city', 'photos', 'benefits']);
            return OfficeSpaceResource::make($officeSpace);
        });
    }
}
