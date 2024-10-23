<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('invoice_bank_transfers', function (Blueprint $table) {
            $table->id();
            $table->integer('invoice_id');
            $table->string('order_id', 100)->unique();
            $table->float('amount', 15, 2);
            $table->string('status',100)->nullable();
            $table->string('receipt')->nullable();
            $table->date('date');
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
        Schema::dropIfExists('invoice_bank_transfers');
    }
};
