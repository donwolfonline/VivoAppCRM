<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateContractsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('contracts', function (Blueprint $table){
            $table->id();
            $table->integer('client')->default(0);
            $table->string('subject')->nullable();
            $table->string('project_id')->nullable();
            $table->float('value')->default(0.00);
            $table->integer('type')->default(0);
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->text('description')->nullable();
            $table->longText('contract_description')->nullable();
            $table->string('status')->default('pending');;
            $table->longText('client_signature')->nullable();
            $table->longText('company_signature')->nullable();
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
        Schema::dropIfExists('contracts');
    }
}
