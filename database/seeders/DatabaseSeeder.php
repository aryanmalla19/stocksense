<?php

namespace Database\Seeders;

use App\Models\Notification;
use App\Models\Portfolio;
use App\Models\Sector;
use App\Models\Stock;
use App\Models\StockPrice;
use App\Models\Transaction;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\UserSetting;
use App\Models\Watchlist;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        DB::table('sectors')->truncate();
        Sector::factory(12)->create();


        Stock::factory(100)->create()->each(function ($stock) {
            StockPrice::factory(5)->create([
                'stock_id' => $stock->id,
                'created_at' => Carbon::now()->subDays(rand(1, 30)), // Random past 30 days
            ]);
        });

        User::factory(15)->create()->each(function ($user) {
            UserSetting::factory()->create(['user_id' => $user->id]);
            Portfolio::factory()->create(['user_id' => $user->id]);
            Transaction::factory(5)->create(['user_id' => $user->id]);

            // Ensure unique stocks in the watchlist
            $stockIds = Stock::inRandomOrder()->limit(4)->pluck('id');
            foreach ($stockIds as $stockId) {
                Watchlist::factory()->create([
                    'user_id' => $user->id,
                    'stock_id' => $stockId,
                ]);
            }
        });


        Notification::factory(10)->create();
    }
}
