<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class AddSourceTypeAndPreviousBalanceToTransactionsTable extends Migration
{
    public function up()
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->integer('source_type')
                ->default(2)
                ->comment('1 = wallet, 2 = non-wallet (online, referral, manual, etc.)')
                ->after('type');

            $table->decimal('previous_balance', 12, 2)
                ->nullable()
                ->comment('Wallet balance before transaction (applicable only if source_type = 1)')
                ->after('amount');
        });

        // Update existing records based on description content
        DB::table('transactions')
            ->where('description', 'LIKE', '%wallet%')
            ->update(['source_type' => 1]);

        // Optional: ensure everything else is explicitly set to 2 (in case default wasn't applied)
        DB::table('transactions')
            ->whereNull('source_type')
            ->orWhere('source_type', '<>', 1)
            ->update(['source_type' => 2]);
    }

    public function down()
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn('source_type');
            $table->dropColumn('previous_balance');
        });
    }
}
