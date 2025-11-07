<?php

namespace Database\Seeders;

use App\Models\IpoApplication;
use App\Models\IpoDetail;
use App\Models\Portfolio;
use App\Models\Stock;
use App\Models\StockPrice;
use App\Models\Transaction;
use App\Models\User;
use App\Models\UserSetting;
use App\Models\Watchlist;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(SectorSeeder::class);

        Stock::factory(15)
//            ->has(StockPrice::factory(10), 'prices')
            ->create();

        $stocks = Stock::all();

        $startDate = Carbon::now()->subMonths(3);
        $endDate = Carbon::now();

        foreach ($stocks as $stock) {
            $date = $startDate->copy();

            while ($date->lte($endDate)) {
                StockPrice::factory()->create([
                    'stock_id' => $stock->id,
                    'date' => $date->copy(),
                ]);

                $date->addDay();
            }
        }

        User::factory(10)->create()->each(function ($user) {
            UserSetting::factory()->create(['user_id' => $user->id]);

            // Create 1 portfolio per user for simplicity
            $portfolio = Portfolio::factory()->create(['user_id' => $user->id]);

            $initialBalance = 100000; // e.g., Rs. 1 lakh
            $portfolio->update(['amount' => $initialBalance]);

            $stockIds = Stock::pluck('id')->toArray();
            $totalSpent = 0;

            // Create transactions (simulate purchases)
            for ($i = 0; $i < 5; $i++) {
                $stockId = Arr::random($stockIds);
                $quantity = rand(1, 50);
                $pricePerUnit = Stock::find($stockId)->prices()->inRandomOrder()->first()?->close_price ?? rand(100, 1000);
                $totalPrice = $quantity * $pricePerUnit;

                // Prevent overspending
                if (($totalSpent + $totalPrice) > $initialBalance) {
                    break;
                }

                $totalSpent += $totalPrice;

                // Create the transaction
                Transaction::factory()->create([
                    'user_id' => $user->id,
                    'stock_id' => $stockId,
                    'quantity' => $quantity,
                    'price' => $pricePerUnit,
                ]);

                // Check if holding already exists
                $holding = DB::table('holdings')
                    ->where('portfolio_id', $portfolio->id)
                    ->where('stock_id', $stockId)
                    ->first();

                // Calculate average price for the stock
                $totalQuantity = $holding ? $holding->quantity + $quantity : $quantity;
                $newAveragePrice = ($holding ? $holding->average_price * $holding->quantity : 0) + ($pricePerUnit * $quantity);
                $newAveragePrice = $newAveragePrice / $totalQuantity;

                if ($holding) {
                    DB::table('holdings')
                        ->where('portfolio_id', $portfolio->id)
                        ->where('stock_id', $stockId)
                        ->update([
                            'quantity' => $totalQuantity,
                            'average_price' => $newAveragePrice,
                        ]);
                } else {
                    DB::table('holdings')->insert([
                        'portfolio_id' => $portfolio->id,
                        'stock_id' => $stockId,
                        'quantity' => $quantity,
                        'average_price' => $pricePerUnit,
                    ]);
                }
            }

            // Deduct the total spent from the balance
            $portfolio->update([
                'amount' => $initialBalance - $totalSpent,
            ]);

            // Create watchlist entries
            $selectedStockIds = collect($stockIds)->shuffle()->take(4);
            foreach ($selectedStockIds as $stockId) {
                Watchlist::factory()->create([
                    'user_id' => $user->id,
                    'stock_id' => $stockId,
                ]);
            }
        });

        $userIds = User::pluck('id')->toArray();

        IpoDetail::factory(5)->create()->each(function ($ipo) use ($userIds) {
            $applyingUsers = collect($userIds)->shuffle()->take(3);

            foreach ($applyingUsers as $userId) {
                IpoApplication::factory()->create([
                    'user_id' => $userId,
                    'ipo_id' => $ipo->id,
                ]);
            }
        });

        // Admin
        User::factory()->create([
            'name' => 'Admin',
            'email' => 'aryanmalla19@gmail.com',
            'password' => Hash::make('Password@123'),
            'email_verified_at' => now(),
            'role' => 'admin',
        ]);
    }
}
