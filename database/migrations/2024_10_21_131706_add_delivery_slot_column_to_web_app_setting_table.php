<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDeliverySlotColumnToWebAppSettingTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('web_app_settings', function (Blueprint $table) {
            //
        });
        DB::table('web_app_settings')->insert([
            [
                'id' => 12, 
                'title' => 'Delivery Slot', 
                'value' => '5:00 AM to 7:00 AM', 
                'created_at' => now(), 
                'updated_at' => now()],
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('web_app_settings', function (Blueprint $table) {
            //
        });
        DB::table('web_app_settings')->where('id', 11)->delete();

    }
}
