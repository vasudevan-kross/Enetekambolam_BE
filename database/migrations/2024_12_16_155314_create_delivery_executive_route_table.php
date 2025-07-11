<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDeliveryExecutiveRouteTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('delivery_executive_route', function (Blueprint $table) {
            $table->engine = 'InnoDB'; // Ensure the table uses InnoDB engine
            $table->id(); // Auto-incrementing primary key for the table
            $table->unsignedBigInteger('delivery_executive_id'); // Foreign key for delivery executive
            $table->unsignedBigInteger('delivery_route_id'); // Foreign key for delivery route
            $table->integer('max_customers')->nullable(); // Maximum customers for the route
            $table->integer('max_orders')->nullable(); // Maximum orders for the route
            $table->integer('priority')->nullable(); // Priority of the route, can now be null
            $table->boolean('is_active')->default(true); // Active status of the entry
            $table->timestamps();

            // Add indexes (optional, but recommended for performance)
            $table->index(['delivery_executive_id', 'delivery_route_id'], 'exec_route_idx');
            $table->index('is_active', 'is_active_idx');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('delivery_executive_route');
    }
}
