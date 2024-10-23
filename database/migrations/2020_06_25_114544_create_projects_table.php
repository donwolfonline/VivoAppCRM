<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProjectsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('projects', function (Blueprint $table){
            $table->id();
            $table->string('title');
            $table->integer('category')->default('0');
            $table->float('price', 15, 2)->default('0.00');
            $table->integer('client')->default('0');
            $table->date('start_date');
            $table->date('due_date');
            $table->string('password')->nullable();
            $table->text('copylinksetting')->nullable();
            $table->text('description')->nullable();
            $table->integer('label')->default('0');
            $table->integer('lead')->default('0');
            $table->string('status', 25)->default('on_going');
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
        Schema::dropIfExists('projects');
    }
}
