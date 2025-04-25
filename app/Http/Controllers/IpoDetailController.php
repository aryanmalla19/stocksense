<?php

namespace App\Http\Controllers;

use App\Enums\IpoDetailStatus;
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
        $ipoDetails = IpoDetail::query();

        if (request('stock_id')) {
            $ipoDetails->stock(request('stock_id'));
        }
        if (request('status')) {
            $ipoDetails->whereIpoStatus(request('status'));
        }

        $ipoDetails->orderBy('close_date', 'desc');

        $ipoDetails = $ipoDetails->get();

        return response()->json([
            'message' => 'Successfully fetched all ipo details',
            'data' => IpoDetailResource::collection($ipoDetails->load(['stock', 'applications'])),
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

        $ipoDetail = IpoDetail::create($data);

        $users = User::get();
        foreach ($users as $user) {
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

        $now = now();

        $openDate = isset($data['open_date']) ? Carbon::parse($data['open_date']) : Carbon::parse($ipoDetail->open_date);
        $closeDate = isset($data['close_date']) ? Carbon::parse($data['close_date']) : Carbon::parse($ipoDetail->close_date);

        if ($openDate->isFuture()) {
            $data['ipo_status'] = IpoDetailStatus::Upcoming;
        } elseif ($now->between($openDate, $closeDate)) {
            $data['ipo_status'] = IpoDetailStatus::Opened;
        } elseif ($closeDate->isPast()) {
            $data['ipo_status'] = IpoDetailStatus::Closed;
        } else {
            $data['ipo_status'] = IpoDetailStatus::Allotted;
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
}
