<?php

namespace App\Console\Commands;

use App\Models\Stock;
use App\Models\StockPrice;
use Carbon\Carbon;
use Illuminate\Console\Command;

class UpdateStockPrices extends Command
{
    protected $signature = 'stocks:update-prices';

    protected $description = 'Update stock prices based on the latest record for each stock';

    public function handle()
    {
        $this->info('⏳ Updating stock prices...');

        $stocks = Stock::with('latestPrice')->get();

        foreach ($stocks as $stock) {
            $latest = $stock->latestPrice;

            if (! $latest) {
                $this->warn("⚠️ No price history for {$stock->symbol}");

                continue;
            }

            // Generate new prices based on previous close
            $openPrice = $latest->close_price;
            $fluctuation = rand(-20, 20) / 1000; // -2% to +2%
            $closePrice = round($openPrice * (1 + $fluctuation), 2);

            // Simulate current price between open and close
            $min = min($openPrice, $closePrice);
            $max = max($openPrice, $closePrice);
            $currentPrice = round($min + ($max - $min) * (rand(30, 70) / 100), 2);

            // Simulate high and low price around current/open/close
            $highPrice = round(max($openPrice, $closePrice, $currentPrice) + rand(1, 3), 2);
            $lowPrice = round(min($openPrice, $closePrice, $currentPrice) - rand(1, 3), 2);

            $newPrice = new StockPrice([
                'open_price' => $openPrice,
                'close_price' => $closePrice,
                'current_price' => $currentPrice,
                'high_price' => $highPrice,
                'low_price' => $lowPrice,
                'volume' => rand(1000, 10000),
                'date' => Carbon::now(),
            ]);

            $stock->prices()->save($newPrice);

            $this->line("💹 {$stock->symbol}: Open {$openPrice} ➡️ Close {$closePrice} | Current: {$currentPrice}");
        }

        $this->info('✅ Stock prices updated successfully!');
    }
}
