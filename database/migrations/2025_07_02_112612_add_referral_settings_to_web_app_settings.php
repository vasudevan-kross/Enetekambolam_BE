<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class AddReferralSettingsToWebAppSettings extends Migration
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
                'id' => 19, 
                'title' => 'Referral Amount', 
                'value' => '100', 
                'created_at' => now(), 
                'updated_at' => now()],
                [
                'id' => 20,     
                'title' => 'Referral Reward on Signup',
                'value' => 'true',
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
        Schema::table('web_app_settings', function (Blueprint $table) {
            //
        });
        DB::table('web_app_settings')->whereIn('id', [19, 20])->delete();
    }
}
