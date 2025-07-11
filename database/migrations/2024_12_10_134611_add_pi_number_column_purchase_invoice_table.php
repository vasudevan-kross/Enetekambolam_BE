<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPiNumberColumnPurchaseInvoiceTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('purchase_invoice', function (Blueprint $table) {
            $table->string('pi_no')->nullable()->after('id');
            $table->double('invoice_amount', 8, 2)->nullable();
            $table->double('return_amount', 8, 2)->nullable();
        });

        Schema::create('purchase_return', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->string('pr_no');
            $table->unsignedBigInteger('pi_id')->nullable();
            $table->string('pi_no');
            $table->unsignedBigInteger('supplier_id')->nullable();
            $table->unsignedBigInteger('warehouse_id')->nullable();
            $table->date('date_of_pr');
            $table->string('city');
            $table->string('pr_status')->nullable();
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->double('total_amount', 8, 2)->nullable();

            $table->foreign('approved_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('supplier_id')->references('id')->on('vendor')->onDelete('set null');
            $table->foreign('warehouse_id')->references('id')->on('warehouse')->onDelete('set null');
            $table->foreign('pi_id')->references('id')->on('purchase_invoice')->onDelete('set null');
        });

        Schema::create('purchase_return_product', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->unsignedBigInteger('pr_id')->nullable();
            $table->unsignedBigInteger('product_id')->nullable();
            $table->double('price', 8, 2);
            $table->string('tax');
            $table->integer('quantity');
            $table->double('amount', 8, 2);
            $table->double('tax_amount', 8, 2);
            $table->double('net_amount', 8, 2);
            $table->string('comments')->nullable();
            $table->foreign('pr_id')->references('id')->on('purchase_return')->onDelete('set null');
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
        Schema::table('purchase_invoice', function (Blueprint $table) {
            $table->dropColumn(['pi_no', 'invoice_amount', 'return_amount']);
        });
        Schema::dropIfExists('purchase_return');
        Schema::dropIfExists('purchase_return_product');
    }
}
