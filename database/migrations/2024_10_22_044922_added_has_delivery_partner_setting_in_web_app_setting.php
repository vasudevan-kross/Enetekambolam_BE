<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;

class AddedHasDeliveryPartnerSettingInWebAppSetting extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Insert the new setting into the web_app_settings table
        DB::table('web_app_settings')->insert([
            'id' => 11,                      // value-1
            'title' => 'HasDeliveryPartner',  // value-2
            'value' => 'false',               // value-3 (set this to 'false' or any other default value)
            'created_at' => now(),            // value-4 (current timestamp)
            'updated_at' => now()             // value-5 (current timestamp)
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Optionally, you can delete the inserted row during rollback
        DB::table('web_app_settings')->where('id', 11)->delete();
    }
}
