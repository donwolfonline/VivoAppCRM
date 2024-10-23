<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProjectMilestonesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('project_milestones', function (Blueprint $table){
            $table->id();
            $table->integer('project_id')->default('0');
            $table->string('title');
            $table->string('status');
            $table->string('progress')->nullable();
            $table->double('cost', 15, 2)->default('0.00');
            $table->date('start_date')->nullable();
            $table->date('due_date');
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
        Schema::dropIfExists('project_milestones');
    }
}
