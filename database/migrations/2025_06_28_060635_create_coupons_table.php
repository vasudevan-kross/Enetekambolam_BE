<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('coupons', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->tinyInteger('type')->default(1)->comment('1 = amount, 2 = percentage');
            $table->decimal('value', 8, 2);
            $table->decimal('min_cart_value', 8, 2)->nullable();
            $table->integer('max_uses_per_user')->nullable();
            $table->boolean('first_time_user_only')->default(false);
            $table->dateTime('start_at')->nullable();
            $table->dateTime('expires_at')->nullable();
            $table->boolean('is_active')->default(true);

            $table->text('description')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('coupons');
    }
};
