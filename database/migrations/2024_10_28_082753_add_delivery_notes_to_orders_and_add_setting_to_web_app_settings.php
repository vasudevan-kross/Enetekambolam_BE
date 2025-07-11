<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class AddDeliveryNotesToOrdersAndAddSettingToWebAppSettings extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Adding the delivery_notes column to orders table
        Schema::table('subscribed_order_delivery', function (Blueprint $table) {
            $table->text('delivery_notes')->nullable()->after('payment_mode');
        });

        // Adding a new setting to web_app_settings table
        DB::table('web_app_settings')->insert([
            'title' => 'Auto Approve',           
            'value' => 'true',              
            'created_at' => now(),
            'updated_at' => now()
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Removing the delivery_notes column from orders table
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('delivery_notes');
        });

        // Optionally delete the specific setting from web_app_settings
        DB::table('web_app_settings')->where('title', 'Auto Approve')->delete(); // use the same title used during insertion
    }
}
