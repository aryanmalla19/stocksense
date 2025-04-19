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
        $query = auth()->user()->ipoApplications();

        if (request()->boolean('is_allotted')) {
            $query->isAllotted();
        }

        $results = $query->get();

        return response()->json([
            'message' => 'Successfully fetched all user ipo applications',
            'data' => IpoApplicationResource::collection($results),
        ]);
    }

    public function store(StoreIpoApplicationRequest $request)
    {
        $user = auth()->user();
        $data = $request->validated();

        // Check if the user has already applied to this IPO
        $alreadyApplied = $user->ipoApplications()
            ->where('ipo_id', $data['ipo_id'])
            ->exists();

        if ($alreadyApplied) {
            return response()->json([
                'message' => 'You have already applied for this IPO.',
            ], 409); // 409 Conflict
        }

        $stockPrice = IpoDetail::findOrFail($data['ipo_id'])->issue_price;
        $totalPrice = $stockPrice * $data['applied_shares'];

        if ($user->portfolio->amount < $totalPrice) {
            return response()->json([
                'message' => 'Insufficient balance',
            ], 400);
        }

        $ipoApplication = $user->ipoApplications()->create($data);

        $user->portfolio()->update([
            'amount' => $user->portfolio->amount - $totalPrice,
        ]);

        return response()->json([
            'message' => 'Successfully IPO applied',
            'data' => new IpoApplicationResource($ipoApplication),
        ]);
    }


    public function show(string $id)
    {
        $ipoApplication = IpoApplication::find($id)->load('ipo');
        $this->authorize('view', [IpoApplication::class, $ipoApplication]);

        return response()->json([
            'message' => 'Successfully fetched all user ipo applications',
            'data' => new IpoApplicationResource($ipoApplication),
        ]);
    }
}
