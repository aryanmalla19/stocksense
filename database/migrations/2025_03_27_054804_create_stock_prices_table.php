<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStockPricesTable extends Migration
{
    public function up()
    {
        Schema::create('stock_prices', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('stock_id')->constrained('stocks')->onDelete('cascade');
            $table->decimal('price', 15, 2);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('stock_prices');
    }
}
