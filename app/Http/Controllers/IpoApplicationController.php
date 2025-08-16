<?php

namespace App\Http\Controllers;

use App\Http\Requests\IPOApplication\StoreIpoApplicationRequest;
use App\Http\Resources\IpoApplicationResource;
use App\Models\IpoApplication;
use App\Models\IpoDetail;

/**
 * @OA\Tag(
 *     name="Ipo Application",
 *     description="Operations related to user Ipo Application"
 * )
 */

/**
 * @OA\Schema(
 *     schema="IpoApplication",
 *     title="IPO Application",
 *     description="IPO Application model",
 *     type="object",
 *     required={"user_id", "ipo_id", "applied_shares", "status", "applied_date", "allotted_shares"},
 *     
 *     @OA\Property(property="id", type="integer", example=1, description="Unique ID of the IPO application"),
 *     @OA\Property(property="user_id", type="integer", example=5, description="User ID who applied"),
 *     @OA\Property(property="ipo_id", type="integer", example=3, description="ID of the IPO"),
 *     @OA\Property(property="applied_shares", type="integer", example=100, description="Number of shares applied for"),
 *     @OA\Property(
 *         property="status",
 *         type="string",
 *         description="Status of the IPO application",
 *         enum={"pending", "allotted", "rejected"},
 *         example="pending"
 *     ),
 *     @OA\Property(property="applied_date", type="string", format="date-time", example="2025-04-26T10:00:00Z", description="Date when IPO was applied"),
 *     @OA\Property(property="allotted_shares", type="integer", example=80, description="Number of shares actually allotted"),
 *     @OA\Property(property="created_at", type="string", format="date-time", example="2025-04-26T10:05:00Z", description="When the record was created"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", example="2025-04-26T11:00:00Z", description="When the record was last updated"),
 * )
 */
class IpoApplicationController extends Controller
{

         /**
     * @OA\Get(
     *     path="/api/ipo-applications",
     *     summary="Get all IPO applications for authenticated user",
     *     description="Returns a list of IPO applications for the authenticated user. You can filter by allotted applications.",
     *     tags={"IPO Applications"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="is_allotted",
     *         in="query",
     *         description="Filter only allotted IPO applications",
     *         required=false,
     *         @OA\Schema(type="boolean")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successfully fetched IPO applications",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Successfully fetched all user ipo applications"),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(ref="#/components/schemas/IpoApplication")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated"),
     * )
     */
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

         /**
     * @OA\Post(
     *     path="/api/ipo-applications",
     *     summary="Apply for an IPO",
     *     description="Create a new IPO application for the authenticated user.",
     *     tags={"IPO Applications"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             required={"ipo_id", "applied_shares"},
     *             @OA\Property(
     *                 property="ipo_id",
     *                 type="integer",
     *                 example=3,
     *                 description="ID of the IPO user wants to apply for"
     *             ),
     *             @OA\Property(
     *                 property="applied_shares",
     *                 type="integer",
     *                 example=100,
     *                 description="Number of shares user wants to apply"
     *             )
     *          )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successfully applied for IPO",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Successfully IPO applied"),
     *             @OA\Property(property="data", ref="#/components/schemas/IpoApplication")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Insufficient balance",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Insufficient balance")
     *         )
     *     ),
     *     @OA\Response(
     *         response=409,
     *         description="Already applied for this IPO",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="You have already applied for this IPO.")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated"),
     * )
     */
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

         /**
     * @OA\Get(
     *     path="/api/ipo-applications/{id}",
     *     summary="Get a specific IPO application by ID",
     *     description="Returns details of a specific IPO application.",
     *     tags={"IPO Applications"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the IPO application",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successfully fetched IPO application",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Successfully fetched all user ipo applications"),
     *             @OA\Property(property="data", ref="#/components/schemas/IpoApplication")
     *         )
     *     ),
     *     @OA\Response(response=403, description="Forbidden - Not authorized to view this resource"),
     *     @OA\Response(response=404, description="IPO Application not found"),
     *     @OA\Response(response=401, description="Unauthenticated"),
     * )
     */
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
