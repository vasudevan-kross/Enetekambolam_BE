<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateTypeColumnInTransactionsTable extends Migration
{
    public function up()
    {
        // Alter the transactions table to update the type column
        Schema::table('transactions', function (Blueprint $table) {
            // Ensure the current `type` column is properly modified
            $table->integer('type')->default(1)->comment('1=credit, 2=debit, 3=refund')->change();
        });
    }

    public function down()
    {
        // Optionally revert back to the original type settings if necessary
        Schema::table('transactions', function (Blueprint $table) {
            $table->integer('type')->default(1)->comment('1=credit, 2=debit')->change();
        });
    }
}

