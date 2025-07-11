<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPoNoAndPiNoToStockApprovalTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('stock_approval', function (Blueprint $table) {
            $table->string('po_no')->nullable(); // Add po_no column as nullable 
            $table->string('pi_no')->nullable(); // Add pi_no column as null
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('stock_approval', function (Blueprint $table) {
            $table->dropColumn('po_no'); // Drop po_no column
            $table->dropColumn('pi_no'); // Drop pi_no column
        });
    }
}
