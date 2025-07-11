<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePurchaseInvoiceTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('purchase_invoice', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('supplier_id')->nullable();
            $table->unsignedBigInteger('warehouse_id')->nullable();
            $table->unsignedBigInteger('purchase_id')->nullable();
            $table->string('po_no')->nullable();
            $table->date('date_of_po');
            $table->string('payment_status')->nullable();
            $table->string('approval_status')->nullable();
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->double('total_amount', 8, 2)->nullable();
            $table->timestamps();

            $table->foreign('supplier_id')->references('id')->on('vendor')->onDelete('set null');
            $table->foreign('warehouse_id')->references('id')->on('warehouse')->onDelete('set null');
            $table->foreign('purchase_id')->references('id')->on('purchars_order')->onDelete('set null');
            $table->foreign('approved_by')->references('id')->on('users')->onDelete('set null');
        });

        Schema::table('purchars_order', function (Blueprint $table) {
           
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->foreign('approved_by')->references('id')->on('users')->onDelete('set null');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('purchase_invoice');

        Schema::table('purchars_order', function (Blueprint $table) {
            $table->dropForeign(['approved_by']);
            $table->dropColumn(['approved_by']);
        });
    }
}
