<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table){
            $table->id();
            $table->string('name');
            $table->string('email');
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('type', 20);
            $table->string('avatar', 100)->nullable();
            $table->string('lang', 100);
            $table->string('mode', 10)->default('light');
            $table->float('storage_limit',8,2)->default(0);
            $table->integer('created_by')->default(0);
            $table->integer('plan')->nullable();
            $table->date('plan_expire_date')->nullable();
            $table->integer('requested_plan')->default(0);
            $table->integer('delete_status')->default(1);
            $table->integer('is_active')->default(1);
            $table->integer('default_pipeline')->nullable();
            $table->string('messenger_color')->default('#6fd943');
            $table->boolean('dark_mode')->default(0);
            $table->boolean('active_status')->default(0);
            $table->datetime('lastlogin')->nullable();
            $table->rememberToken();
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
        Schema::dropIfExists('users');
    }
}
