<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use App\Models\DeliveryZone;
use Laravel\Sanctum\Sanctum;
use App\Models\Restaurant;
use App\Models\User;
use Tests\TestCase;

class DeliveryZoneControllerTest extends TestCase
{
	use RefreshDatabase;

    public function test_admin_create_delivery_zone(): void
    {
    	$user = User::factory()->create();
        $restaurant = Restaurant::factory()->create();

        Sanctum::actingAs($user, [], 'admin');

        // Radius based zone create
        $radiusPayload = [
            'type' => DeliveryZone::RADIUS,
            'restaurant_id' => $restaurant->id,
            'radius_km' => 5,
            'center_lat' => 23.7808,
            'center_lng' => 90.2792,
        ];

        $radiusResponse = $this->postJson('/api/admin/delivery-zones', $radiusPayload);

    	$radiusResponse->assertStatus(200)
                    ->assertJsonFragment([
                        'type' => DeliveryZone::RADIUS,
                    ]);

        $this->assertDatabaseHas('delivery_zones', [
            'restaurant_id' => $restaurant->id,
            'type' => DeliveryZone::RADIUS,
            'radius_km' => 5,
        ]);

        // Polygon based zone create with existing polygon
        DeliveryZone::create([
            'restaurant_id' => $restaurant->id,
            'type' => DeliveryZone::POLYGON,
            'coordinates' => json_encode([
                ['lat' => 23.8103, 'lng' => 90.4125],
                ['lat' => 23.8110, 'lng' => 90.4150],
                ['lat' => 23.8090, 'lng' => 90.4175],
                ['lat' => 23.8075, 'lng' => 90.4130],
            ]),
        ]);

        $polygonPayload = [
            'type' => DeliveryZone::POLYGON,
            'restaurant_id' => $restaurant->id,
            'coordinates' => json_encode([
                ['lat' => 23.79, 'lng' => 90.29],
            ]),
        ];

        $polygonResponse = $this->postJson('/api/admin/delivery-zones', $polygonPayload);

        $polygonResponse->assertStatus(200)
                    ->assertJsonFragment([
                        'type' => DeliveryZone::POLYGON,
                    ]);

        $deliveryZone = DeliveryZone::where('restaurant_id', $restaurant->id)
			                        ->where('type', DeliveryZone::POLYGON)
			                        ->first();

		$coordinates = $deliveryZone->coordinates;

        $merged = json_decode($coordinates, true);

        // Merged: old + new
        $this->assertCount(5, $merged);
    }
}
