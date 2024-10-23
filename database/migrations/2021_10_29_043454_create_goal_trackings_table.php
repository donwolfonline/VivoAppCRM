<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGoalTrackingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('goal_trackings', function (Blueprint $table) {
            $table->id();
            $table->integer('branch');
            $table->integer('goal_type');
            $table->date('start_date');
            $table->date('end_date');
            $table->string('subject');
            $table->string('rating')->nullable();
            $table->string('target_achievement');
            $table->string('description');
            $table->integer('status')->nullable();
            $table->integer('progress')->nullable();
            $table->integer('created_by');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('goal_trackings');
    }
}
