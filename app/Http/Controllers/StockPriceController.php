<?php

namespace App\Http\Controllers;

use App\Http\Requests\StockPrice\StoreStockPriceRequest;
use App\Http\Resources\StockPriceResource;
use App\Http\Resources\StockResource;
use App\Http\Resources\StockWithPriceResource;
use App\Models\Stock;
use App\Models\StockPrice;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class StockPriceController extends Controller
{
    public function index()
    {
        $stocks = Stock::with('prices')->get();

        return response()->json([
            'message' => 'Successfully fetched all stock with its prices',
            'data' => StockWithPriceResource::collection($stocks),
        ]);
    }

    public function store(StoreStockPriceRequest $request)
    {
        $data = $request->validated();

        $newPrice = StockPrice::create([
            'stock_id' => $data['stock_id'],
            'current_price' => $data['current_price'],
            'open_price' => $data['open_price'] ?? $data['current_price'],
            'close_price' => $data['close_price'] ?? null,
            'high_price' => $data['high_price'] ?? $data['current_price'],
            'low_price' => $data['low_price'] ?? $data['current_price'],
            'volume' => $data['volume'] ?? 0,
            'date' => $data['date'] ?? now(),
        ]);

        return response()->json([
            'message' => 'Successfully created new stock price',
            'data' => new StockPriceResource($newPrice),
        ], 201);
    }


    public function show(string $id)
    {
        $stockPrice = StockPrice::with('stock')->where('id', $id)->first();
        if (empty($stockPrice)) {
            return response()->json([
                'message' => 'Could not find stock price data with ID ' . $id,
            ], 404);
        }

        return response()->json([
            'message' => 'Successfully fetched stock price data with ID ' . $id,
            'data' => new StockPriceResource($stockPrice),
        ]);
    }

    public function update(Request $request, string $id)
    {
        return response()->json([
            'message' => 'You cannot change stock previous price',
        ], 400);
    }

    public function destroy(string $id)
    {
        return response()->json([
            'message' => 'You cannot delete stock previous price',
        ], 400);
    }


    public function historyStockPrices($id)
    {
        $stock = Stock::with('prices')->find($id);

        if (!$stock) {
            return response()->json(['message' => 'Stock not found'], 404);
        }
        return new StockResource($stock->load(['prices', 'sector']));
    }

    public function historyStockPricesLive($id)
    {
        $stock = Stock::with('prices')->find($id);

        if (!$stock) {
            return response()->json(['message' => 'Stock not found'], 404);
        }

        $response = new StreamedResponse(function () use ($stock, $id) {
            echo "data: " . json_encode([
                    'type' => 'initial',
                    'data' => $stock
                ]) . "\n\n";
            ob_flush();
            flush();

            while (true) {
                $latestPrice = $stock->latestPrice;
                echo "data: " . json_encode([
                        'type' => 'update',
                        'data' => $latestPrice
                    ]) . "\n\n";

                ob_flush();
                flush();

                sleep(5);
            }
        });

        // Proper SSE headers
        $response->headers->set('Content-Type', 'text/event-stream');
        $response->headers->set('Cache-Control', 'no-cache');
        $response->headers->set('Connection', 'keep-alive');
        $response->headers->set('X-Accel-Buffering', 'no'); // nginx: turn off response buffering

        return $response;
    }

}
