<?php

namespace App\Http\Controllers;

use App\Http\Requests\IPOApplication\StoreIpoApplicationRequest;
use App\Http\Resources\IpoApplicationResource;

use App\Models\IpoApplication;
use Carbon\Carbon;

class IpoApplicationController extends Controller
{
    public function store(StoreIpoApplicationRequest $request)
    {
        $data = $request->validated();

        $data['applied_date'] = Carbon::now();
        $ipoApplication = IpoApplication::create($data);
        
        return response()->json([
            'message' => 'Successfully ipo applied',
            'data' => new IpoApplicationResource($ipoApplication),
        ]);
    }
}
