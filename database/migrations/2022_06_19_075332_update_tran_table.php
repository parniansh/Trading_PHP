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
        Schema::table('user_wallet_transactions', function (Blueprint $table) {
            //
            $table->integer('execution_id');
            $table->integer('payment_id');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('user_wallet_transactions', function (Blueprint $table) {
            //
            $table->dropColumn('execution_id');
            $table->dropColumn('payment_id');

        });
    }
};
