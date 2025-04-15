<?php

namespace App\Http\Controllers;

use App\Http\Requests\IPOApplication\StoreIpoApplicationRequest;
use App\Http\Resources\IpoApplicationResource;
use App\Models\IpoApplication;

class IpoApplicationController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $ipoApplications = $user->ipoApplications;

        return response()->json([
            'message' => 'Successfully fetched all user ipo applications',
            'data' => IpoApplicationResource::collection($ipoApplications),
        ]);
    }

    public function store(StoreIpoApplicationRequest $request)
    {
        $ipoApplication = IpoApplication::create($request->validated());

        return response()->json([
            'message' => 'Successfully ipo applied',
            'data' => new IpoApplicationResource($ipoApplication),
        ]);
    }
}
