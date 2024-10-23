<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(
            'items', function (Blueprint $table){
            $table->id();
            $table->string('name');
            $table->string('sku');
            $table->float('sale_price',15,2)->default('0.00');
            $table->float('purchase_price',15,2)->default('0.00');
            $table->integer('quantity')->default('1');
            $table->string('tax')->nullable();
            $table->integer('category')->default('0');
            $table->integer('unit');
            $table->string('type');
            $table->text('description')->nullable();
            $table->integer('created_by');
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
        Schema::dropIfExists('items');
    }
}
