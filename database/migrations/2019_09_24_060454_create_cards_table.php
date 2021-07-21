<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCardsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cards', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('customer_id');
            $table->foreign('customer_id')->references('id')->on('customers')->unsigned();
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('card_number')->nullable();
            $table->string('card_exp')->nullable();
            $table->string('card_type')->nullable();
            $table->string('shopify_customer_id')->nullable();
            $table->string('payment_profile_id')->nullable();
            $table->string('gateway_customer_profile_id')->nullable();
            $table->string('gateway')->nullable();
            $table->string('validated')->nullable();
            $table->string('validated_exp')->nullable();
            $table->dateTime('validated_at')->nullable();
            $table->timestamps();
        });
        
        Schema::table('customers', function (Blueprint $table) {
            $table->unsignedBigInteger('card_id')->after('id')->nullable();
            $table->foreign('card_id')->references('id')->on('cards')->unsigned();
        });

         Schema::table('customers', function (Blueprint $table) {
            $table->unsignedInteger('shop_id')->after('id');
            $table->foreign('shop_id')->references('id')->on('shops')->unsigned();
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->unsignedBigInteger('card_id')->after('id')->nullable();
            $table->foreign('card_id')->references('id')->on('cards')->unsigned();
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->unsignedBigInteger('customer_id')->after('id')->nullable();
            $table->foreign('customer_id')->references('id')->on('customers')->unsigned();
        });
        
        
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('cards', function (Blueprint $table) {
            $table->dropForeign('cards_customer_id_foreign');
            $table->dropColumn(['customer_id']);
        });

        Schema::table('customers', function (Blueprint $table) {
            $table->dropForeign('customers_card_id_foreign');
            $table->dropColumn(['card_id']);
        });

        Schema::table('customers', function (Blueprint $table) {
            $table->dropForeign('customers_shop_id_foreign');
            $table->dropColumn(['shop_id']);
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign('orders_card_id_foreign');
            $table->dropColumn(['card_id']);
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign('orders_customer_id_foreign');
            $table->dropColumn(['customer_id']);
        });

        Schema::dropIfExists('cards');
    }
}
