<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDefaultNotificationTypeToSpecificNotificationTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('specific_notification', function (Blueprint $table) {
            // Add a new column for notification type with a default value of 'wallet'
            $table->string('notification_type')->default('wallet')->nullable()->after('body'); // `nullable()` ensures existing records are compatible
        });
    }

    public function down()
    {
        Schema::table('specific_notification', function (Blueprint $table) {
            // Drop the column if we roll back the migration
            $table->dropColumn('notification_type');
        });
    }
}
