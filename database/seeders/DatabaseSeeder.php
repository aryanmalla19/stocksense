<?php

namespace Database\Seeders;

use App\Models\IpoApplication;
use App\Models\IpoDetail;
use App\Models\Portfolio;
use App\Models\Sector;
use App\Models\Stock;
use App\Models\StockPrice;
use App\Models\Transaction;
use App\Models\User;
use App\Models\UserSetting;
use App\Models\Watchlist;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        DB::table('sectors')->truncate();
        // Fixing the typo and ensuring correct sector count
        Sector::factory(11)->sequence(
            ['name' => 'banking'],
            ['name' => 'hydropower'],
            ['name' => 'life Insurance'],
            ['name' => 'health'],
            ['name' => 'manufacturing'],
            ['name' => 'hotel'],
            ['name' => 'trading'],
            ['name' => 'microfinance'],
            ['name' => 'finance'],
            ['name' => 'investment'],
            ['name' => 'others'],
        )->create();

        // Optimized stock seeding
        Stock::factory(50)
            ->has(StockPrice::factory(5), 'prices')
            ->create();

        // Seed users & their related models
        User::factory(10)->create()->each(function ($user) {
            UserSetting::factory()->create(['user_id' => $user->id]);
            Portfolio::factory(3)->create(['user_id' => $user->id]);
            Transaction::factory(5)->create(['user_id' => $user->id]);

            // Get all available stock IDs.
            $stockIds = Stock::pluck('id')->toArray();

            // Prevent errors if there are fewer stocks than needed
            $selectedStockIds = Arr::random($stockIds, min(4, count($stockIds)));

            foreach ($selectedStockIds as $stockId) {
                Watchlist::factory()->create([
                    'user_id'  => $user->id,
                    'stock_id' => $stockId,
                ]);
            }
        });

        // Seed IPO details & applications
        IpoDetail::factory(5)
            ->has(IpoApplication::factory(3), 'applications')
            ->create();
    }
}
