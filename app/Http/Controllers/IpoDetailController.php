<?php

namespace App\Http\Controllers;

use App\Http\Requests\IPODetails\StoreIpoDetailRequest;
use App\Http\Requests\IPODetails\UpdateIpoDetailRequest;
use App\Http\Resources\IpoDetailResource;
use App\Models\IpoDetail;
use App\Models\User;
use App\Notifications\IpoCreated;
use Carbon\Carbon;

class IpoDetailController extends Controller
{
    public function index()
    {
        $ipoDetails = IpoDetail::query(); // 👈 important

        if (request('stock_id')) {
            $ipoDetails->stock(request('stock_id'));
        }

        $ipoDetails = $ipoDetails->get(); // 👈 now fetch results

        return response()->json([
            'message' => 'Successfully fetched all ipo details',
            'data' => IpoDetailResource::collection($ipoDetails->load('stock')),
        ]);
    }

    public function show($id)
    {
        // Eager load the 'stock' and 'sector' relationships
        $ipoDetail = IpoDetail::with(['stock.sector', 'stock.latestPrice'])->find($id);

        if (! $ipoDetail) {
            return response()->json([
                'message' => 'IPO detail not found for '.$id,
            ], 404);
        }

        return response()->json([
            'message' => 'Successfully fetched ipo details',
            'data' => new IpoDetailResource($ipoDetail),
        ]);
    }

    public function store(StoreIpoDetailRequest $request)
    {
        $data = $request->validated();

        $openDate = Carbon::parse($data['open_date']);
        $closeDate = Carbon::parse($data['close_date']);
        $listingDate = Carbon::parse($data['listing_date']);
        $now = now();

        if ($openDate->isFuture()) {
            $data['ipo_status'] = 'pending';
        } elseif ($now->between($openDate, $closeDate)) {
            $data['ipo_status'] = 'opened';
        } elseif ($closeDate->isPast()) {
            $data['ipo_status'] = 'closed';
        } else {
            $data['ipo_status'] = 'unknown';
        }

        $ipoDetail = IpoDetail::create($data);

        $users = User::get();
        foreach($users as $user){
            $user->notify(new IpoCreated($ipoDetail));
        }

        return response()->json([
            'message' => 'Successfully created new IPO detail',
            'data' => new IpoDetailResource($ipoDetail),
        ]);
    }

    public function update(UpdateIpoDetailRequest $request, $id)
    {
        $ipoDetail = IpoDetail::find($id);

        if (! $ipoDetail) {
            return response()->json([
                'message' => 'IPO detail not found for ID: '.$id,
            ], 404);
        }

        $data = $request->validated();

        // Recalculate the ipo_status if date fields are present
        $now = now();

        $openDate = isset($data['open_date']) ? Carbon::parse($data['open_date']) : Carbon::parse($ipoDetail->open_date);
        $closeDate = isset($data['close_date']) ? Carbon::parse($data['close_date']) : Carbon::parse($ipoDetail->close_date);
        $listingDate = isset($data['listing_date']) ? Carbon::parse($data['listing_date']) : Carbon::parse($ipoDetail->listing_date);

        if ($openDate->isFuture()) {
            $data['ipo_status'] = 'pending';
        } elseif ($now->between($openDate, $closeDate)) {
            $data['ipo_status'] = 'opened';
        } elseif ($closeDate->isPast()) {
            $data['ipo_status'] = 'closed';
        } else {
            $data['ipo_status'] = 'unknown';
        }

        $ipoDetail->update($data);

        return response()->json([
            'message' => 'Successfully updated IPO detail',
            'data' => new IpoDetailResource($ipoDetail),
        ]);
    }

    public function destroy($id)
    {
        $ipoDetail = IpoDetail::find($id);

        if (! $ipoDetail) {
            return response()->json([
                'message' => 'Could not find IPO detail with ID: '.$id,
            ], 404);
        }

        $ipoDetail->delete();

        return response()->json([
            'message' => 'Successfully deleted IPO detail with ID: '.$id,
        ]);
    }

    public function adminIndex()
    {
        $user = auth()->user();

        $ipoDetails = IpoDetail::query();

        if (request('stock_id')) {
            $ipoDetails->stock(request('stock_id'));
            $ipoDetails->with(['applications', 'stock']);
        }
        $ipoDetails = $ipoDetails->get();

        return response()->json([
            'message' => 'Successfully fetched all IPO details with applications',
            'data' => IpoDetailResource::collection($ipoDetails),
        ]);
    }

}
