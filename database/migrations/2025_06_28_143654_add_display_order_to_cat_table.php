<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('cat', function (Blueprint $table) {
            $table->integer('display_order')->default(0)->after('title');
        });

        // Set display_order = id for existing records
        DB::statement('UPDATE cat SET display_order = id');
    }

    public function down(): void
    {
        Schema::table('cat', function (Blueprint $table) {
            $table->dropColumn('display_order');
        });
    }
};
