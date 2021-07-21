<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->json('draft_order');
            $table->string('draft_id');
            $table->string('draft_name');
            $table->json('order')->nullable();
            $table->string('order_id')->nullable();
            $table->string('order_name')->nullable();
            $table->decimal('amount', 10, 2);
            $table->string('email')->nullable();
            $table->string('shopify_status');
            $table->string('gateway_status');
            $table->string('gateway_transaction_id')->nullable();
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
        Schema::dropIfExists('orders');
    }
}
