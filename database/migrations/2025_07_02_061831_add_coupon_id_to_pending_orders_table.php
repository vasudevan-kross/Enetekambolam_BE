<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCouponIdToPendingOrdersTable extends Migration
{
    public function up()
    {
        Schema::table('pending_orders', function (Blueprint $table) {
            $table->unsignedBigInteger('coupon_id')->nullable()->after('address_id');

            // Optional: add foreign key if coupon table exists
            $table->foreign('coupon_id')->references('id')->on('coupons')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::table('pending_orders', function (Blueprint $table) {
            $table->dropForeign(['coupon_id']);
            $table->dropColumn('coupon_id');
        });
    }
}
