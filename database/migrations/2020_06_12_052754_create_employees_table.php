<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEmployeesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('employees', function (Blueprint $table){
            $table->id();
            $table->integer('user_id')->default('0');
            $table->integer('employee_id')->default('0');
            $table->string('name')->nullable();
            $table->date('dob')->nullable();
            $table->integer('branch_id')->default(0);
            $table->integer('department')->default('0');
            $table->integer('designation')->default('0');
            $table->date('joining_date')->nullable();
            $table->date('exit_date')->nullable();
            $table->string('gender')->nullable();
            $table->text('address')->nullable();
            $table->string('mobile')->nullable();
            $table->string('salary_type')->nullable();
            $table->integer('salary')->default('0');
            $table->string('emergency_contact')->nullable();
            $table->string('account_holder_name')->nullable();
            $table->string('account_number')->nullable();
            $table->string('bank_name')->nullable();
            $table->string('bank_identifier_code')->nullable();
            $table->string('branch_location')->nullable();
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
        Schema::dropIfExists('employees');
    }
}
