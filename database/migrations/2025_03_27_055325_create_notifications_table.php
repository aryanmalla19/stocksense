<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateNotificationsTable extends Migration
{
    public function up()
    {
        // Create the enum type if it doesn't exist
        DB::statement("DO $$ BEGIN
            IF NOT EXISTS (SELECT 1 FROM pg_type WHERE typname = 'notification_type') THEN
                CREATE TYPE notification_type AS ENUM ('portfolio_update', 'system');
            END IF;
        END $$;");

        // Create the table without the type column
        Schema::create('notifications', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->text('message');
            $table->boolean('is_read')->default(false);
            $table->timestamps();
        });

        // Add the type column after the table is created
        DB::statement('ALTER TABLE notifications ADD COLUMN type notification_type NOT NULL');
    }

    public function down()
    {
        Schema::dropIfExists('notifications');
        DB::statement("DROP TYPE IF EXISTS notification_type");
    }
}