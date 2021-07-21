<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('shop_id');
            $table->foreign('shop_id')->references('id')->on('shops')->unsigned();
            $table->string('gateway')->nullable();
            $table->string('authorize_payment_api_login_id')->nullable();
            $table->string('authorize_payment_transaction_key')->nullable();
            $table->string('authorize_key')->nullable();
            $table->string('stripe_published_key')->nullable();
            $table->string('stripe_secret_key')->nullable();
            $table->json('user_interface')->nullable();
            $table->json('user_experience')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('settings');
    }
}
