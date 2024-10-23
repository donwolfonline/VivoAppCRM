<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEstimateProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(
            'estimate_products', function (Blueprint $table){
            $table->id();
            $table->integer('estimate');
            $table->integer('item');
            $table->integer('quantity');
            $table->float('discount')->default('0.00');
            $table->float('price',15,2)->default('0.00');
            $table->string('tax')->nullable();
            $table->text('description')->nullable();
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
        Schema::dropIfExists('estimate_products');
    }
}
