<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDeliveryExecutiveTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('delivery_executive', function (Blueprint $table) {
            $table->string('executive_id');
            $table->id();
            $table->string('name'); 
            $table->string('email'); 
            $table->string('phn_no1');
            $table->string('phn_no2');
            $table->date('dob');
            $table->string('country');
            $table->string('state');  
            $table->string('district');  
            $table->string('address');  
            $table->string('doc_type'); 
            $table->string('doc_no');  
            $table->string('vehicle_no');  
            $table->string('vehicle_ins_no'); 
            $table->date('vehicle_ins_exp_date');  
            $table->string('personal_ins_no')->nullable();  
            $table->integer('deposit_amt')->nullable();  
            $table->date('deposit_date')->nullable();  
            $table->string('deposit_receipt_no')->nullable();  
            $table->string('bank'); 
            $table->string('account_no');  
            $table->string('branch_name')->nullable();  
            $table->string('branch_address')->nullable(); 
            $table->string('ifsc'); 
            $table->string('upi')->nullable();  
            $table->string('city');  
            $table->date('doj');  
            $table->string('uid')->nullable(); 
            $table->json('remuneration_model'); 
            $table->string('referred_by')->nullable();  
            $table->string('specific_product_inclusion')->nullable();  
            $table->text('comments')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::table('images', function (Blueprint $table) {
            $table->integer('image_type')
                ->default(1)
                ->comment('1=profile_image, 2=slider_image, 3=checkBook_image, 4=agreement_image, 5=panCard_image, 6=gst_image, 7=fssi_image, 8=executive_image, 9=doc_image')
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
        Schema::dropIfExists('delivery_executive');
        
        Schema::table('images', function (Blueprint $table) {
            $table->integer('image_type')
                ->default(1)
                ->comment('1=profile_image, 2=slider_image, 3=checkBook_image, 4=agreement_image, 5=panCard_image, 6=gst_image, 7=fssi_image')
                ->change();
        });
    }
}
