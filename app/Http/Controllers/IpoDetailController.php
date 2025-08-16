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

/**
 * @OA\Tag(
 *     name="IpoDetails",
 *     description="Operations related to Ipo"
 * )
 */

/**
 * @OA\Schema(
 *     schema="IpoDetail",
 *     title="IPO Detail",
 *     description="IPO Detail model",
 *     type="object",
 *     required={"stock_id", "issue_price", "total_shares", "open_date", "close_date", "listing_date", "ipo_status"},
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="stock_id", type="integer", example=5, description="ID of the related stock"),
 *     @OA\Property(property="issue_price", type="number", format="float", example=100.50, description="Issue price of the IPO"),
 *     @OA\Property(property="total_shares", type="integer", example=10000, description="Total number of shares issued"),
 *     @OA\Property(property="open_date", type="string", format="date-time", example="2025-05-01T09:00:00Z", description="IPO open date"),
 *     @OA\Property(property="close_date", type="string", format="date-time", example="2025-05-10T17:00:00Z", description="IPO close date"),
 *     @OA\Property(property="listing_date", type="string", format="date-time", example="2025-05-15T10:00:00Z", description="Listing date on the exchange"),
 *     @OA\Property(
 *         property="ipo_status",
 *         type="string",
 *         description="Current status of the IPO",
 *         enum={"Upcoming", "Opened", "Closed", "Allotted"},
 *         example="Upcoming"
 *     ),
 *     @OA\Property(property="created_at", type="string", format="date-time", example="2025-04-26T10:00:00Z"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", example="2025-04-26T12:00:00Z"),
 * )
 */
class IpoDetailController extends Controller
{
     /**
     * @OA\Get(
     *     path="/ipo-details",
     *     summary="Get all IPO details",
     *     tags={"IPO Details"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="stock_id",
     *         in="query",
     *         description="Filter by Stock ID",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         description="Filter by IPO Status",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successfully fetched all IPO details",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Successfully fetched all ipo details"),
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/IpoDetail"))
     *         )
     *     )
     * )
     */
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

     /**
     * @OA\Get(
     *     path="/ipo-details/{id}",
     *     summary="Get a specific IPO detail",
     *     tags={"IPO Details"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="IPO Detail ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successfully fetched ipo details",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Successfully fetched ipo details"),
     *             @OA\Property(property="data", ref="#/components/schemas/IpoDetail")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="IPO detail not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="IPO detail not found for 1")
     *         )
     *     )
     * )
     */
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

     /**
     * @OA\Post(
     *     path="/ipo-details",
     *     summary="Create a new IPO detail",
     *     tags={"IPO Details"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             required={"stock_id", "issue_price", "total_shares", "open_date", "close_date", "listing_date"},
     *             @OA\Property(
     *                 property="stock_id",
     *                 type="integer",
     *                 description="ID of the stock"
     *             ),
     *             @OA\Property(
     *                 property="issue_price",
     *                 type="number",
     *                 format="float",
     *                 description="The issue price of the IPO"
     *             ),
     *             @OA\Property(
     *                 property="total_shares",
     *                 type="integer",
     *                 description="The total shares for the IPO"
     *             ),
     *             @OA\Property(
     *                 property="open_date",
     *                 type="string",
     *                 format="date-time",
     *                 description="The open date of the IPO"
     *             ),
     *             @OA\Property(
     *                 property="close_date",
     *                 type="string",
     *                 format="date-time",
     *                 description="The close date of the IPO"
     *             ),
     *             @OA\Property(
     *                 property="listing_date",
     *                 type="string",
     *                 format="date-time",
     *                 description="The listing date of the IPO"
     *             ),
     *             @OA\Property(
     *                 property="ipo_status",
     *                 type="string",
     *                 enum={"Upcoming", "Opened", "Closed", "Allotted"},
     *                 description="The status of the IPO"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Successfully created new IPO detail",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Successfully created new IPO detail"),
     *             @OA\Property(property="data", ref="#/components/schemas/IpoDetail")
     *         )
     *     )
     * )
     */
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

     /**
     * @OA\Put(
     *     path="/ipo-details/{id}",
     *     summary="Update an existing IPO detail",
     *     tags={"IPO Details"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="IPO Detail ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/IpoDetail")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successfully updated IPO detail",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Successfully updated IPO detail"),
     *             @OA\Property(property="data", ref="#/components/schemas/IpoDetail")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="IPO detail not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="IPO detail not found for ID: 1")
     *         )
     *     )
     * )
     */
    public function update(UpdateIpoDetailRequest $request, $id)
    {
        $ipoDetail = IpoDetail::find($id);

        if (! $ipoDetail) {
            return response()->json([
                'message' => 'IPO detail not found for ID: '.$id,
            ], 404);
        }

        if($ipoDetail->ipo_status === IpoDetailStatus::Allotted){
            return response()->json([
                'message' => 'You cannot change IPO that is already allotted'
            ], 400);
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

     /**
     * @OA\Delete(
     *     path="/ipo-details/{id}",
     *     summary="Delete an IPO detail",
     *     tags={"IPO Details"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="IPO Detail ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successfully deleted IPO detail",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Successfully deleted IPO detail with ID: 1")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="IPO detail not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Could not find IPO detail with ID: 1")
     *         )
     *     )
     * )
     */
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
