<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCouponDiscountValueToOrdersPendingOrdersAndUsages extends Migration
{
    public function up()
    {
        // Add to orders table
        Schema::table('orders', function (Blueprint $table) {
            $table->decimal('coupon_discount_value', 10, 2)->default(0);
        });

        // Add to pending_orders table
        Schema::table('pending_orders', function (Blueprint $table) {
            $table->decimal('coupon_discount_value', 10, 2)->default(0);
        });

        // Add to coupon_usages table
        Schema::table('coupon_usages', function (Blueprint $table) {
            $table->decimal('coupon_discount_value', 10, 2)->default(0);
        });
    }

    public function down()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('coupon_discount_value');
        });

        Schema::table('pending_orders', function (Blueprint $table) {
            $table->dropColumn('coupon_discount_value');
        });

        Schema::table('coupon_usages', function (Blueprint $table) {
            $table->dropColumn('coupon_discount_value');
        });
    }
}
