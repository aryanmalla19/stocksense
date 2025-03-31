<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStocksTable extends Migration
{
    public function up()
    {
        Schema::create('stocks', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('symbol')->unique();
            $table->string('company_name');
            $table->foreignId('sector_id')->nullable()->constrained('sectors')->onDelete('set null');
            $table->text('description')->nullable();
            $table->decimal('current_price');
            $table->boolean('is_ipo')->default(false);
            $table->enum('ipo_status', ['pending', 'opened', 'closed'])->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('stocks');
    }
}
