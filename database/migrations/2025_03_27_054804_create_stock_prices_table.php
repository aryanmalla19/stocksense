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
            $table->decimal('open_price');
            $table->decimal('close_price');
            $table->decimal('high_price');
            $table->decimal('low_price');
            $table->integer('volume');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('stock_prices');
    }
}
