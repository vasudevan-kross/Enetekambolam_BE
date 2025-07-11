<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class MakePhnNo2NullableInDeliveryExecutiveTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('delivery_executive', function (Blueprint $table) {
            $table->string('phn_no2')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('delivery_executive', function (Blueprint $table) {
            $table->string('phn_no2')->nullable(false)->change();
        });
    }
}
