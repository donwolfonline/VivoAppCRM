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
        if(!Schema::hascolumn('plans','trial')) {

        Schema::table('plans', function (Blueprint $table) {
            $table->integer('trial')->default(0)->after('max_client');
            $table->string('trial_days')->nullable()->after('trial');
        });
    }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('plans', function (Blueprint $table) {
            //
        });
    }
};

