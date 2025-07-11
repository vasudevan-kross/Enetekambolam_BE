<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateTablesForExecutiveAndAssignRole extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Add 'executive_id' column to 'assign_role' table
        Schema::table('assign_role', function (Blueprint $table) {
            $table->string('executive_id')->nullable()->after('user_id');
            // Make 'user_id' nullable
            $table->unsignedBigInteger('user_id')->nullable()->change();
        });        
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Reverse 'executive_id' column addition
        Schema::table('assign_role', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->nullable(false)->change();
            $table->dropColumn('executive_id');
        });
    }
}
