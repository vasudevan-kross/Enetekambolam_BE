<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddUidAndStatusToUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('uid')->nullable()->after('id');
            $table->tinyInteger('status')->default(0)->after('isotpverified'); // Default 0 for active
            $table->dropUnique('users_email_unique');
        });

        Schema::table('user_address', function (Blueprint $table) {
            $table->string('area')->nullable()->change();
            $table->string('landmark')->nullable()->change();
            $table->string('pincode')->nullable()->change();
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
            $table->dropColumn('uid');
            $table->dropColumn('status');
            $table->unique('email');
        });

        Schema::table('user_address', function (Blueprint $table) {
            $table->string('area')->nullable(false)->change();
            $table->string('landmark')->nullable(false)->change();
            $table->string('pincode')->nullable(false)->change();
        });
    }
}
