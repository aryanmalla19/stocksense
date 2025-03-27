<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateTransactionsTable extends Migration
{
    public function up()
    {
        // Create the enum type in PostgreSQL
        DB::statement("CREATE TYPE transaction_type AS ENUM ('buy', 'sell')");

        // Create the table without the type column
        Schema::create('transactions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('stock_id')->constrained('stocks')->onDelete('cascade');
            $table->integer('quantity');
            $table->decimal('price', 15, 2);
            $table->timestamp('timestamp');
            $table->index('user_id', 'idx_user_transaction');
            $table->timestamps();
        });

        // Add the type column after the table is created
        DB::statement('ALTER TABLE transactions ADD COLUMN type transaction_type NOT NULL');
    }

    public function down()
    {
        Schema::dropIfExists('transactions');
        DB::statement("DROP TYPE IF EXISTS transaction_type");
    }
}