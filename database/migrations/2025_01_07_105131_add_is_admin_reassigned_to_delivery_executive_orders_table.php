<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIsAdminReassignedToDeliveryExecutiveOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('delivery_executive_orders', function (Blueprint $table) {
            $table->boolean('is_admin_reassigned')->default(false);
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
            $table->dropColumn('is_admin_reassigned');
        });
    }
}
