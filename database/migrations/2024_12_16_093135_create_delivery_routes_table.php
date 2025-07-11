<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDeliveryRoutesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('delivery_routes', function (Blueprint $table) {
            $table->id(); // Auto-incrementing primary key
            $table->string('route_name'); // Name of the route
            $table->bigInteger('pincode'); // Pincode for the route (changed to BIGINT)
            $table->string('city_name'); // Name of the city (mandatory)
            $table->decimal('latitude', 10, 6)->nullable(); // Latitude of the route (nullable)
            $table->decimal('longitude', 10, 6)->nullable(); // Longitude of the route (nullable)
            $table->json('locations')->nullable(); // Locations stored as JSON (nullable)
            $table->boolean('is_active')->default(true); // Column for active status (default is true)
            $table->timestamps(); // Timestamps for created_at and updated_at
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('delivery_routes');
    }
}
