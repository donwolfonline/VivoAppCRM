<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProposalsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(
            'proposals', function (Blueprint $table){
            $table->id();
            $table->unsignedBigInteger('proposal');
            $table->text('subject')->nullable();
            $table->string('related');
            $table->unsignedBigInteger('client');
            $table->date('issue_date');
            $table->date('open_till');
            $table->date('send_date')->nullable();
            $table->integer('category');
            $table->integer('status')->default('0');
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
        Schema::dropIfExists('proposals');
    }
}
