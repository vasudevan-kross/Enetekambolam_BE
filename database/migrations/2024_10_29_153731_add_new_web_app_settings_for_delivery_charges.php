<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddNewWebAppSettingsForDeliveryCharges extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Insert initial settings
        DB::table('web_app_settings')->insert([
            [
                'id' => 15,     
                'title' => 'Order Timing',
                'value' => '9:00 AM to 9:00 PM',  // Adjust the value as necessary
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'id' => 16,     
                'title' => 'Delivery Charge (Buy Once)',
                'value' => '25',  // Adjust the value as necessary
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'id' => 17,     
                'title' => 'Delivery Charge (Subscription)',
                'value' => '8',  // Adjust the value as necessary
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'id' => 18,     
                'title' => 'Free Delivery Min. Limit (Subscription)',
                'value' => '200',
                'created_at' => now(),
                'updated_at' => now()
            ]
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('web_app_settings')->whereIn('title', [
            'Order Timing',
            'Delivery Charge (Buy Once)',
            'Delivery Charge (Subscription)',
            'Free Delivery Min. Limit (Subscription)'
        ])->delete();
    }
}
