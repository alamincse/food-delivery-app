<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('delivery_zones', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('restaurant_id');
            $table->enum('type', ['polygon', 'radius']);
            $table->json('coordinates')->nullable(); // For polygon coordinates(custom-drawn areas)
            $table->decimal('radius_km', 8, 2)->nullable(); // For radius based zones
            $table->decimal('center_lat', 10, 6)->nullable();
            $table->decimal('center_lng', 10, 6)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('delivery_zones');
    }
};
