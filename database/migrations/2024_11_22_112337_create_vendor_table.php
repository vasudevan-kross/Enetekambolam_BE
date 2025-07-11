<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVendorTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('vendor', function (Blueprint $table) {
            $table->id('id'); 
            $table->string('supplier_name');
            $table->string('user_name');
            $table->string('office_ph_no');
            $table->string('poc_name');
            $table->string('poc_ph_no');
            $table->string('poc_email');
            $table->string('fssai')->nullable();
            $table->string('arn')->nullable();
            $table->string('pan')->nullable();
            $table->string('gst_no')->nullable();
            $table->string('gst_state_code')->nullable();
            $table->string('country');
            $table->string('state');
            $table->string('district');
            $table->string('pincode')->nullable();
            $table->text('address')->nullable();
            $table->string('uid');
            $table->boolean('is_price_edit')->default(false);
            $table->string('outlet')->nullable();
            $table->string('bankName');
            $table->string('ac_no');
            $table->string('ifsc');
            $table->string('branch_name');
            $table->text('branch_address');
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
        Schema::dropIfExists('vendor');
    }
}
