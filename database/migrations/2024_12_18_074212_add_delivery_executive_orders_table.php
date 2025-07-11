<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDeliveryExecutiveOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('delivery_executive_orders', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('delivery_executive_id')->nullable();
            $table->unsignedBigInteger('order_id')->nullable();
            $table->string('executive_number')->nullable();
            $table->string('order_number')->nullable();
            $table->date('assigned_date');  
            $table->boolean('is_reassign_requested')->default(false);
            $table->timestamps();
            $table->foreign('delivery_executive_id')->references('id')->on('delivery_executive')->onDelete('set null');
            $table->foreign('order_id')->references('id')->on('orders')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('delivery_executive_orders');
    }
}
