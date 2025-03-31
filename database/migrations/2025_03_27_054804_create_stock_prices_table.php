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
            $table->decimal('open_price',15,2);
            $table->decimal('close_price',15,2)->nullable();
            $table->decimal('high_price',15,2);
            $table->decimal('low_price',15,2);
            $table->integer('volume');
            $table->timestamp('date');
        });
    }

    public function down()
    {
        Schema::dropIfExists('stock_prices');
    }
}
