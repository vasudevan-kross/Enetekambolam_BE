<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePendingOrdersTable extends Migration
{
    public function up()
    {
        Schema::create('pending_orders', function (Blueprint $table) {
            $table->id();

            // User & Address
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('address_id')->nullable();

            // Razorpay Details
            $table->string('razorpay_order_id')->unique();
            $table->string('payment_id')->nullable();
            $table->string('payment_type')->nullable();
            $table->string('payment_description')->nullable();
            $table->tinyInteger('payment_mode')->default(0);
            $table->enum('payment_status', ['pending', 'paid', 'failed'])->default('pending');
            $table->string('status')->nullable();

            // Order Info
            $table->decimal('order_amount', 10, 2);
            $table->decimal('delivery_charge', 10, 2)->default(0);
            $table->string('order_type')->nullable();
            $table->date('start_date')->nullable();
            $table->text('delivery_instruction')->nullable();

            // Subscription Specific
            $table->string('subscription_type')->nullable();
            $table->string('selected_days_for_weekly')->nullable();
            $table->string('qty')->nullable();
            $table->decimal('mrp', 10, 2)->nullable();
            $table->decimal('price', 10, 2)->nullable();
            $table->decimal('tax', 10, 2)->nullable();
            $table->unsignedBigInteger('product_id')->nullable();
            $table->json('product_details')->nullable();

            $table->decimal('wallet_added_amount', 10, 2)->default(0);
            $table->timestamps();
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->string('razorpay_order_id')->nullable()->unique()->after('trasation_id');
        });
    }

    public function down()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('razorpay_order_id');
        });

        Schema::dropIfExists('pending_orders');
    }
}
