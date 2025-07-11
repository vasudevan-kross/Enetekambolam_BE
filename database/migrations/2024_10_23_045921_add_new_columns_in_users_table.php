<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddNewColumnsInUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            // Add otp and isotpverified columns
            $table->string('otp')->nullable();
            $table->boolean('isotpverified')->default(false);

            // Update the name column to be nullable
            $table->string('name')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            // Drop otp and isotpverified columns
            $table->dropColumn('otp');
            $table->dropColumn('isotpverified');

            // Revert the name column to not nullable
            $table->string('name')->nullable(false)->change();
        });
    }
}
