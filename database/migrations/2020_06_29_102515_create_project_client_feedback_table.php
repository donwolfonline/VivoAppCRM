<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProjectClientFeedbackTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(
            'project_client_feedback', function (Blueprint $table){
            $table->id();
            $table->integer('project_id');
            $table->string('file');
            $table->text('feedback')->nullable();
            $table->integer('feedback_by')->default('0');
            $table->integer('parent')->default('0');
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
        Schema::dropIfExists('project_client_feedback');
    }
}
