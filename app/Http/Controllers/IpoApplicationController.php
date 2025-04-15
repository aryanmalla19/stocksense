<?php

namespace App\Http\Controllers;

use App\Http\Requests\IPOApplication\StoreIpoApplicationRequest;
use App\Http\Resources\IpoApplicationResource;
use App\Models\IpoApplication;
use App\Models\IpoDetail;

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
        $user = auth()->user();
        $data = $request->validated();

        $stockPrice = IpoDetail::findOrFail($data['ipo_id'])->issue_price;
        $totalPrice = $stockPrice * $data['applied_shares'];

        if($user->portfolio->amount < $totalPrice){
            return response()->json([
                'message' => 'Insufficient balance',
            ], 400);
        }

        $ipoApplication = $user->ipoApplications()->create($data);

        $user->portfolio()->update([
            'amount' => $user->portfolio->amount - $totalPrice,
        ]);

        return response()->json([
            'message' => 'Successfully ipo applied',
            'data' => new IpoApplicationResource($ipoApplication),
        ]);
    }

    public function show(string $id)
    {
        $ipoApplication = IpoApplication::find($id)->load('ipo');
        return response()->json([
            'message' => 'Successfully fetched all user ipo applications',
            'data' => new IpoApplicationResource($ipoApplication),
        ]);
    }
}
