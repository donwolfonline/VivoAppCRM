<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePaymentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(
            'payments', function (Blueprint $table){
            $table->id();
            $table->date('date');
            $table->float('amount', 15, 2)->default('0.00');
            $table->integer('client');
            $table->integer('payment_method');
            $table->string('reference');
            $table->text('description')->nullable();
            $table->integer('created_by')->default('0');
            $table->string('receipt')->nullable();
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
        Schema::dropIfExists('payments');
    }
}
