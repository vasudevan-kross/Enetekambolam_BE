<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddUpdatedStockDeliveryExecutiveOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('delivery_executive_orders', function (Blueprint $table) {
            $table->json('updated_stock')->nullable();
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('updated_stock');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('delivery_executive_orders', function (Blueprint $table) {
            $table->dropColumn('updated_stock');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->json('updated_stock')->nullable()->after('address_id');
        });
    }
}
