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
        Schema::table('appointments', function (Blueprint $table) {
            $table->string('checkin_token')->nullable()->unique()->after('status');
            $table->timestamp('checked_in_at')->nullable()->after('checkin_token');
            $table->string('queue_number')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->dropColumn(['checkin_token', 'checked_in_at']);
            $table->integer('queue_number')->nullable()->change();
        });
    }
};
