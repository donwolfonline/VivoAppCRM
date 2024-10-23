<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProjectTasksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('project_tasks', function (Blueprint $table){
            $table->id();
            $table->string('title');
            $table->string('priority');
            $table->text('description')->nullable();
            $table->date('due_date')->nullable();
            $table->date('start_date')->nullable();
            $table->integer('hours')->default('0');
            $table->integer('assign_to');
            $table->integer('project_id');
            $table->integer('milestone_id')->default(0);
            $table->integer('created_by')->default('0');
            $table->string('status')->default('todo');
            $table->integer('stage')->default(0);
            $table->integer('order')->default(0);
            $table->integer('time_tracking')->default(0);
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
        Schema::dropIfExists('project_tasks');
    }
}
