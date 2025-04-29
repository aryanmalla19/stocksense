<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('ipo_details', function (Blueprint $table) {
            $table->timestamp('opening_notified_at')->nullable();
            $table->timestamp('closing_notified_at')->nullable();
            $table->timestamp('listing_notified_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ipo_details', function (Blueprint $table) {
            $table->dropColumn('opening_notified_at');
            $table->dropColumn('closing_notified_at');
            $table->dropColumn('listing_notified_at');
        });
    }
    
};
