<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBillsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(
            'bills', function (Blueprint $table){
            $table->id();
            $table->string('bill')->default('0');
            $table->integer('vender');
            $table->date('bill_date');
            $table->date('due_date');
            $table->integer('order_number')->default('0');
            $table->integer('status')->default('0');
            $table->date('send_date')->nullable();
            $table->integer('category_id');
            $table->integer('discount_apply')->default('0');
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
        Schema::dropIfExists('bills');
    }
}
