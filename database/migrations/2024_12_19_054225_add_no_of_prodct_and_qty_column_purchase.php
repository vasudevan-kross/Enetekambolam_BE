<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddNoOfProdctAndQtyColumnPurchase extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('purchars_order', function (Blueprint $table) {
            $table->integer('no_of_products')->nullable();
            $table->integer('no_of_qty')->nullable();
            $table->string('commands')->nullable();
        });
        Schema::table('purchase_invoice', function (Blueprint $table) {
            $table->integer('no_of_products')->nullable();
            $table->integer('no_of_qty')->nullable();
            $table->string('commands')->nullable();
            $table->unsignedBigInteger('pr_id')->nullable();
            $table->string('pr_no')->nullable();
            $table->foreign('pr_id')->references('id')->on('purchase_return')->onDelete('set null');
        });
        Schema::table('purchase_return', function (Blueprint $table) {
            $table->integer('no_of_products')->nullable();
            $table->integer('no_of_qty')->nullable();
            $table->string('commands')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('purchars_order', function (Blueprint $table) {
            $table->dropColumn('no_of_products', 'no_of_qty', 'commands');
        });
        Schema::table('purchase_invoice', function (Blueprint $table) {
            $table->dropForeign(['pr_id']);
            $table->dropColumn('no_of_products', 'no_of_qty', 'commands', 'pr_id', 'pr_no');
        });
        Schema::table('purchase_return', function (Blueprint $table) {
            $table->dropColumn('no_of_products', 'no_of_qty', 'commands');
        });
    }
}
