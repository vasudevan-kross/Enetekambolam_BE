<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateSubscribedOrderDeliveryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('subscribed_order_delivery', function (Blueprint $table) {
            // Make entry_user_id nullable 
            $table->unsignedBigInteger('entry_user_id')->nullable()->change();
             // Add executive_id column
              $table->unsignedBigInteger('executive_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('subscribed_order_delivery', function (Blueprint $table) {
            $table->unsignedBigInteger('entry_user_id')->nullable(false)->change(); 
            $table->dropColumn('executive_id');
        });
    }
}
