<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePurcharsOrderAndPurchaseProductTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('purchars_order', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->string('po_no');
            $table->unsignedBigInteger('supplier_id')->nullable();
            $table->unsignedBigInteger('warehouse_id')->nullable();
            $table->string('city');
            $table->date('date_of_po');
            $table->date('date_of_delivery');
            $table->string('delivery_time');
            $table->string('po_status')->nullable();
            $table->string('pi_status')->nullable();
            $table->string('po_type')->nullable();
            $table->double('total_amount', 8, 2)->nullable();

            $table->foreign('supplier_id')->references('id')->on('vendor')->onDelete('set null');
            $table->foreign('warehouse_id')->references('id')->on('warehouse')->onDelete('set null');
        });

        Schema::create('purchase_product', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->unsignedBigInteger('purchase_id')->nullable();
            $table->unsignedBigInteger('product_id')->nullable();
            $table->double('price', 8, 2);
            $table->string('tax');
            $table->integer('quantity');
            $table->double('amount', 8, 2);
            $table->double('tax_amount', 8, 2);
            $table->double('net_amount', 8, 2);
            $table->string('comments')->nullable();
            $table->foreign('purchase_id')->references('id')->on('purchars_order')->onDelete('set null');
            $table->foreign('product_id')->references('id')->on('product')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('purchars_order');
        Schema::dropIfExists('purchase_product');
        
    }
}
