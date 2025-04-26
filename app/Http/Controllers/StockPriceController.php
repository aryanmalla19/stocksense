<?php

namespace App\Http\Controllers;

use App\Http\Resources\StockResource;
use App\Models\Stock;
use Symfony\Component\HttpFoundation\StreamedResponse;

class StockPriceController extends Controller
{
    public function historyStockPrices($id)
    {
        $stock = Stock::with('prices')->find($id);

        if (! $stock) {
            return response()->json(['message' => 'Stock not found'], 404);
        }

        return new StockResource($stock->load(['prices', 'sector']));
    }
    public function historyStockPricesLive($id)
    {
        $response = new StreamedResponse(function () use ($id) {
            ignore_user_abort(true);
            set_time_limit(0);
            ob_end_clean();
            header('Content-Encoding: none');

            $stock = Stock::with('prices')->find($id);
            echo "data: ".json_encode(['type' => 'initial', 'data' => $stock])."\n\n";
            ob_flush(); flush();

            while (true) {
                $latest = Stock::find($id)?->latestPrice;
                if ($latest) {
                    echo "data: ".json_encode(['type' => 'update', 'data' => $latest])."\n\n";
                }

                echo "event: ping\ndata: {}\n\n";
                ob_flush(); flush();
                sleep(5);
            }
        });


        $response->headers->set('Content-Type', 'text/event-stream');
        $response->headers->set('Cache-Control', 'no-cache');
        $response->headers->set('Connection', 'keep-alive');
        $response->headers->set('X-Accel-Buffering', 'no');

        return $response;
    }

}
