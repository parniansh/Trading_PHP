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
        Schema::create('referral', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id')->unsigned();
            $table->string('referral_code'); 
            $table->integer('parent_id');
            $table->string('category_serial');
            $table->string('parent_referral_code');

        });
        Schema::table('referral', function($table) {
            $table->foreign('user_id')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('referral');
    }
};