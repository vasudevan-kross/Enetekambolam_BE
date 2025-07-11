<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddImageTypeForVendor extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('images', function (Blueprint $table) {
            $table->integer('image_type')
          ->default(1)
          ->comment('1=profile_image, 2=slider_image, 3=checkBook_image, 4=agreement_image, 5=panCard_image, 6=gst_image, 7=fssi_image') 
          ->change();      
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('images', function (Blueprint $table) {
            $table->integer('image_type')
          ->default(1)
          ->comment('1=profile_image, 2=slider_image') 
          ->change(); 
        });
    }
}
