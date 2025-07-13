<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Notifications\OrderAssignedNotification;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Hash;
use App\Models\DeliveryZone;
use Laravel\Sanctum\Sanctum;
use App\Models\DeliveryMan;
use App\Models\Restaurant;
use App\Models\Order;
use App\Models\User;
use Tests\TestCase;

class OrderControllerTest extends TestCase
{
	use RefreshDatabase;

	public function test_admin_can_create_order_inside_radius_delivery_zone(): void
    {
    	Notification::fake();

        $user = User::factory()->create();

        Sanctum::actingAs($user, [], 'admin');

        $restaurant = Restaurant::factory()->create();

        // Create Radius delivery zone
        $zone = DeliveryZone::create([
            'restaurant_id' => $restaurant->id,
            'type' => DeliveryZone::RADIUS,
            'radius_km' => 5,
            'center_lat' => 23.7808,
            'center_lng' => 90.2792,
        ]);

		// Delivery man available nearby
		$deliveryMan = DeliveryMan::create([
		                'name' => 'Rahim', 
		                'email' => 'rahim@gmail.com',
		                'password' => Hash::make('1234'),
		                'latitude' => 23.7810, 
		                'longitude' => 90.2795, 
		                'is_available' => true
		            ]);

        // Order payload
        $orderPayload = [
        	'user_id' => auth('admin')->id(),
            'restaurant_id' => $restaurant->id,
            'delivery_men_id' => $deliveryMan->id,
            'status' => Order::PENDING,
            'delivery_lat' => 23.7811,
            'delivery_lng' => 90.2796,
            'total_amount' => 150,
        ];

        $response = $this->postJson('/api/admin/orders', $orderPayload);

        $response->assertStatus(200)
        		->assertJsonFragment([
        			'message' => 'Order placed and waiting for the delivery man confirmation.',
        		]);

        // Ensure order was saved
        $this->assertDatabaseHas('orders', $orderPayload);

        // Notification Send
        $deliveryMan->notify(new OrderAssignedNotification($orderPayload));

	    Notification::assertSentTo(
	        $deliveryMan,
	        OrderAssignedNotification::class
	    );
    }

    public function test_admin_can_create_order_inside_polygon_delivery_zone(): void
	{
	    Notification::fake();

	    $admin = User::factory()->create();
	    Sanctum::actingAs($admin, [], 'admin');

	    $restaurant = Restaurant::factory()->create();

	    // Create Polygon delivery zone
	    $polygon = [
	        ['lat' => 23.7800, 'lng' => 90.2780],
	        ['lat' => 23.7800, 'lng' => 90.2820],
	        ['lat' => 23.7840, 'lng' => 90.2820],
	        ['lat' => 23.7840, 'lng' => 90.2780],
	    ];

	    DeliveryZone::create([
	        'restaurant_id' => $restaurant->id,
	        'type' => DeliveryZone::POLYGON,
	        'coordinates' => json_encode($polygon),
	    ]);

	    // Delivery man available nearby
	    $deliveryMan = DeliveryMan::create([
	        'name' => 'Rahim',
	        'email' => 'rahim@gmail.com',
	        'password' => Hash::make('1234'),
	        'latitude' => 23.7810,
	        'longitude' => 90.2790,
	        'is_available' => true
	    ]);


	    // Order payload
        $orderPayload = [
        	'user_id' => auth('admin')->id(),
            'restaurant_id' => $restaurant->id,
            'delivery_men_id' => $deliveryMan->id,
            'status' => Order::PENDING,
            'delivery_lat' => 23.7820, // Inside the polygon
	        'delivery_lng' => 90.2800,
	        'total_amount' => 200,
        ];

	    $response = $this->postJson('/api/admin/orders', $orderPayload);

	    $response->assertStatus(200)
		        ->assertJsonFragment([
		            'message' => 'Order placed and waiting for the delivery man confirmation.',
		        ]);

	    $this->assertDatabaseHas('orders', $orderPayload);

	    // Notification send
		$deliveryMan->notify(new OrderAssignedNotification($orderPayload));

	    Notification::assertSentTo(
	        $deliveryMan,
	        OrderAssignedNotification::class
	    );
	}

	public function test_it_returns_error_if_no_delivery_zone_exists(): void
	{
	    $user = User::factory()->create();

	    Sanctum::actingAs($user, [], 'admin');

	    $restaurant = Restaurant::factory()->create();

	    // No DeliveryZone created

	    $orderPayload = [
	        'user_id' => auth('admin')->id(),
            'restaurant_id' => $restaurant->id,
            'status' => Order::PENDING,
	        'delivery_lat' => 23.78,
	        'delivery_lng' => 90.28,
	        'total_amount' => 100,
	    ];

	    $response = $this->postJson('/api/admin/orders', $orderPayload);

	    $response->assertStatus(400)
	    		->assertJsonFragment([
			        'message' => 'Delivery address is outside the delivery zone',
			        'status' => 'FAILED'
			    ]);
	}

	public function test_it_returns_error_if_no_delivery_man_available(): void
	{
	    $admin = User::factory()->create();
	    Sanctum::actingAs($admin, [], 'admin');

	    $restaurant = Restaurant::factory()->create();

	    // Create delivery zone inside valid area
	    DeliveryZone::create([
	        'restaurant_id' => $restaurant->id,
	        'type' => DeliveryZone::RADIUS,
	        'radius_km' => 10,
	        'center_lat' => 23.78,
	        'center_lng' => 90.28,
	    ]);

	    // No available delivery man created

	    $orderPayload = [
	        'user_id' => auth('admin')->id(),
            'restaurant_id' => $restaurant->id,
            'status' => Order::PENDING,
	        'delivery_lat' => 23.78,
	        'delivery_lng' => 90.28,
	        'total_amount' => 100,
	    ];

	    $response = $this->postJson('/api/admin/orders', $orderPayload);

	    $response->assertStatus(400)
		    	->assertJsonFragment([
			        'message' => 'No delivery person available nearby',
			        'status' => 'FAILED'
			    ]);
	}

	public function test_it_returns_error_if_coordinates_outside_delivery_zone(): void
	{
	    $admin = User::factory()->create();
	    Sanctum::actingAs($admin, [], 'admin');

	    $restaurant = Restaurant::factory()->create();

	    // Far away delivery zone
	    DeliveryZone::create([
	        'restaurant_id' => $restaurant->id,
	        'type' => DeliveryZone::RADIUS,
	        'radius_km' => 5,
	        'center_lat' => 23.70, // far away
	        'center_lng' => 90.20,
	    ]);

	    // Coordinates far from zone center
	    $orderPayload = [
	        'user_id' => auth('admin')->id(),
            'restaurant_id' => $restaurant->id,
            'status' => Order::PENDING,
	        'delivery_lat' => 24.00,
	        'delivery_lng' => 90.50,
	        'total_amount' => 100,
	    ];

	    $response = $this->postJson('/api/admin/orders', $orderPayload);

	    $response->assertStatus(400)
		    	->assertJsonFragment([
			        'message' => 'Delivery address is outside the delivery zone',
			        'status' => 'FAILED'
			    ]);
	}
}
