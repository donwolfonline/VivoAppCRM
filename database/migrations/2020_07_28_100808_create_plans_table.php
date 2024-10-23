<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePlansTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(
            'plans', function (Blueprint $table){
            $table->id();
            $table->string('name', 100);
            $table->float('price')->default(0);
            $table->string('duration', 100);
            $table->integer('max_employee')->default(0);
            $table->integer('max_client')->default(0);
            $table->text('description')->nullable();
            $table->string('image')->nullable();
            $table->float('storage_limit',8,2)->default(0);
            $table->string('enable_chatgpt')->nullable();
            $table->timestamps();
        }
        );
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('plans');
    }
}
