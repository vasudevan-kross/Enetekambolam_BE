<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddNewFieldsInProductAndVendorTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Update the 'products' table
        Schema::table('product', function (Blueprint $table) {
            $table->string('sku')->nullable()->after('id');
            $table->string('expire_days')->nullable();
            $table->string('storage_type')->nullable();
            $table->double('min_cart_qty', 8, 2)->nullable();
            $table->double('max_cart_qty', 8, 2)->nullable();
            $table->double('daily_sales_limit', 8, 2)->nullable();
            $table->boolean('is_active')->default(true);
            $table->string('status')->nullable();
            $table->unsignedBigInteger('vendor_id')->nullable();
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->double('purchase_price', 8, 2)->nullable();
            $table->double('margin_percent', 5, 2)->nullable();
            $table->double('margin_amt', 8, 2)->nullable();
            $table->string('margin_type')->nullable();

            // Foreign key for 'vendor_id'
            $table->foreign('vendor_id')->references('id')->on('vendor')->onDelete('set null');
            $table->foreign('approved_by')->references('id')->on('users')->onDelete('set null');
        });

        // Update the 'vendors' table
        Schema::table('vendor', function (Blueprint $table) {
            $table->boolean('is_active')->default(true)->after('id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Rollback changes in 'products' table
        Schema::table('product', function (Blueprint $table) {
            $table->dropForeign(['vendor_id']);
            $table->dropForeign(['approved_by']);
            $table->dropColumn([
                'sku',
                'expire_days',
                'storage_type',
                'min_cart_qty',
                'max_cart_qty',
                'daily_sales_limit',
                'is_active',
                'status',
                'vendor_id',
                "approved_by",
                'purchase_price',
                'margin_percent',
                'margin_amt',
                'margin_type'
            ]);
        });

        // Rollback changes in 'vendors' table
        Schema::table('vendor', function (Blueprint $table) {
            $table->dropColumn('is_active');
        });
    }
}
