<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreIpoApplicationRequest;
use App\Http\Resources\IpoApplicationResource;
use App\Models\IpoApplication;

class IpoApplicationController extends Controller
{
    public function store(StoreIpoApplicationRequest $request)
    {
        $ipoApplication = IpoApplication::create($request->validated());

        return response()->json([
            'message' => 'Successfully ipo applied',
            'data' => new IpoApplicationResource($ipoApplication),
        ]);
    }
}
