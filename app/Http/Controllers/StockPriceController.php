<?php

namespace App\Http\Controllers;

use App\Http\Resources\StockResource;
use App\Models\Stock;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * @OA\Schema(
 *     schema="StockPrice",
 *     type="object",
 *     title="Stock Price",
 *     description="Schema for a stock price record",
 * )
 */
class StockPriceController extends Controller
{
     /**
     * @OA\Get(
     *     path="/api/v1/stocks/{id}/history",
     *     summary="Get historical prices of a stock",
     *     description="Fetch the stock along with all its past prices and sector information.",
     *     tags={"Stock Prices"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the stock",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful fetch of stock history",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="data", ref="#/components/schemas/StockPrice")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Stock not found",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Stock not found")
     *         )
     *     ),
     *     security={{"bearerAuth":{}}}
     * )
     */
    public function historyStockPrices($id)
    {
        $stock = Stock::with(['prices', 'sector'])->find($id);

        if (! $stock) {
            return response()->json(['message' => 'Stock not found'], 404);
        }

        $filteredPrices = $stock->prices
            ->groupBy(function ($price) {
                return Carbon::parse($price->date)->toDateString();
            })
            ->map(function ($dailyPrices) {
                return $dailyPrices->sortByDesc('date')->first();
            })
            ->values();

        $stock->setRelation('prices', $filteredPrices);

        return new StockResource($stock);
    }

     /**
     * @OA\Get(
     *     path="/api/v1/stocks/{id}/history/live",
     *     summary="Get live historical stock prices (via SSE)",
     *     description="Provides a Server-Sent Events (SSE) stream of stock prices in real-time.",
     *     tags={"Stock Prices"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the stock",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Stream of stock price updates",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="type", type="string", example="initial|update"),
     *             @OA\Property(property="data", ref="#/components/schemas/StockPrice")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Stock not found",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Stock not found")
     *         )
     *     ),
     *     security={{"bearerAuth":{}}}
     * )
     */
    public function historyStockPricesLive($id)
    {
        // Ensure the stock exists
        $stock = Stock::with('prices')->find($id);
        if (!$stock) {
            return response()->json(['error' => 'Stock not found'], 404);
        }

        $response = new StreamedResponse(function () use ($id, $stock) {
            // Ignore user abort to continue running after client disconnect
            ignore_user_abort(true);

            // Prevent script timeout
            set_time_limit(0);

            // Start output buffering explicitly
            if (!ob_get_level()) {
                ob_start();
            }

            // Send initial data
            echo "data: " . json_encode(['type' => 'initial', 'data' => $stock]) . "\n\n";
            if (ob_get_length()) {
                ob_flush();
            }
            flush();

            while (true) {
                // Fetch the latest price
                $latest = Stock::find($id)?->latestPrice;
                if ($latest) {
                    echo "data: " . json_encode(['type' => 'update', 'data' => $latest]) . "\n\n";
                }

                // Send ping event to keep connection alive
                echo "event: ping\ndata: {}\n\n";

                // Flush output to client
                if (ob_get_length()) {
                    ob_flush();
                }
                flush();

                // Wait before sending the next update
                sleep(5);

                // Check for client disconnection
                if (connection_aborted()) {
                    break;
                }
            }

            // Clean up output buffer
            if (ob_get_level()) {
                ob_end_flush();
            }
        }, 200);

        // Set SSE headers
        $response->headers->set('Content-Type', 'text/event-stream');
        $response->headers->set('Cache-Control', 'no-cache');
        $response->headers->set('Connection', 'keep-alive');
        $response->headers->set('X-Accel-Buffering', 'no'); // Disable Nginx buffering
        $response->headers->set('Content-Encoding', 'none'); // Avoid compression

        return $response;
    }


    public function stream()
    {
        // Use StreamedResponse for better streaming control
        return new StreamedResponse(function () {
            // Start output buffering explicitly
            if (!ob_get_level()) {
                ob_start();
            }

            // Set SSE headers
            header('Content-Type: text/event-stream');
            header('Cache-Control: no-cache');
            header('Connection: keep-alive');
            header('X-Accel-Buffering: no'); // Disable Nginx buffering

            while (true) {
                // Send SSE data
                echo "data: " . json_encode(['message' => 'Update at ' . now()]) . "\n\n";

                // Flush output
                if (ob_get_length()) {
                    ob_flush();
                }
                flush();

                // Sleep to simulate periodic updates
                sleep(1);

                // Check for connection abort
                if (connection_aborted()) {
                    break;
                }
            }

            // Clean up output buffer
            if (ob_get_level()) {
                ob_end_flush();
            }
        }, 200);
    }


}
