<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('coupons', function (Blueprint $table) {
            $table->decimal('max_discount_amount', 8, 2)
                ->nullable()
                ->after('value')
                ->comment('Maximum discount allowed if type is percentage');
        });
    }

    public function down(): void
    {
        Schema::table('coupons', function (Blueprint $table) {
            $table->dropColumn('max_discount_amount');
        });
    }
};
