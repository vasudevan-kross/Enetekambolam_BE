<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIsConformAndReassignedExecutiveIdToDeliveryExecutiveOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('delivery_executive_orders', function (Blueprint $table) {
            $table->boolean('is_conform')->default(true);
            $table->string('reassigned_executive_id')->nullable();
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
            $table->dropColumn('is_conform');
            $table->dropColumn('reassigned_executive_id');
        });
    }
}
