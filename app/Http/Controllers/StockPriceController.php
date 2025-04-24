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
