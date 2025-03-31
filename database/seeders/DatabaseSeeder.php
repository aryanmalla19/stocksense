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
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {

        // Seed stocks & stock prices
        Stock::factory(50)->create()->each(fn ($stock) => StockPrice::factory(5)->create(['stock_id' => $stock->id])
        );

        // Seed users & their related models
        User::factory(10)->create()->each(function ($user) {
            UserSetting::factory()->create(['user_id' => $user->id]);
            Portfolio::factory(3)->create(['user_id' => $user->id]);
            Transaction::factory(5)->create(['user_id' => $user->id]);
            Watchlist::factory(4)->create(['user_id' => $user->id]);
        });

        // Seed IPO details & applications
        IpoDetail::factory(5)->create()->each(fn ($ipo) => IpoApplication::factory(3)->create(['ipo_id' => $ipo->id])
        );
    }
}
