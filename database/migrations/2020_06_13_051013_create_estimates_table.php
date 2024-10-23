<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEstimatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(
            'estimates', function (Blueprint $table){
            $table->id();
            $table->unsignedBigInteger('estimate');
            $table->unsignedBigInteger('client');
            $table->date('issue_date');
            $table->date('expiry_date');
            $table->date('send_date')->nullable();
            $table->integer('category');
            $table->integer('status')->default('0');
            $table->integer('discount_apply')->default('0');
            $table->integer('is_convert')->default('0');
            $table->integer('created_by')->default('0');
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
        Schema::dropIfExists('estimates');
    }
}
