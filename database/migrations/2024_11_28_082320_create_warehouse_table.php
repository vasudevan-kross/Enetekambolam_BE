<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWarehouseTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('warehouse', function (Blueprint $table) {
            $table->id('id'); 
            $table->string('uid');
            $table->string('warehouse_name');
            $table->string('email');
            $table->string('phone_no');
            $table->string('poc_name');
            $table->string('poc_ph_no');
            $table->string('poc_email');
            $table->string('gst_no');
            $table->string('fssai');
            $table->string('billing_address');
            $table->string('country');
            $table->string('state');
            $table->string('district');
            $table->string('pincode');
            $table->text('address');
            $table->string('latitude');
            $table->string('longitude');
            $table->string('service_city');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('warehouse');
    }
}
