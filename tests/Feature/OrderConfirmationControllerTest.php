<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;
use App\Models\DeliveryMan;
use App\Models\Restaurant;
use App\Models\Order;
use App\Models\User;
use Tests\TestCase;

class OrderConfirmationControllerTest extends TestCase
{
	use RefreshDatabase; 

    public function test_delivery_man_can_confirm_his_own_assigned_order(): void
	{
		$restaurant = Restaurant::factory()->create();

		$user = User::factory()->create();
	    
	    $deliveryMan = DeliveryMan::create([
		                'name' => 'Rahim', 
		                'email' => 'rahim@gmail.com',
		                'password' => Hash::make('1234'),
		                'latitude' => 23.7810, 
		                'longitude' => 90.2795, 
		                'is_available' => true
		            ]);

	    config(['sanctum.guard' => ['web', 'delivery_man', 'admin']]);

	    Sanctum::actingAs($deliveryMan, [], 'delivery_man');

	    $order = Order::create([
	    	'user_id' => $user->id,
	    	'restaurant_id' => $restaurant->id,
	        'delivery_men_id' => $deliveryMan->id,
	        'status' => Order::PENDING,
	    ]);

	    $payload = [
		        'order_id' => $order->id,
		        'status' => Order::APPROVED, 
		    ];

	    $response = $this->postJson('/api/delivery/orders/status', $payload);

	    $response->assertStatus(200)
		        ->assertJsonFragment([
		            'data' => 'Order approved',
				    'message' => 'Success',
				    'status' => 'SUCCESS',
		        ]);

	    $this->assertDatabaseHas('orders', [
	        'id' => $order->id,
	        'status' => Order::APPROVED,
	    ]);
	}

	public function test_delivery_man_cannot_confirm_order_not_assigned_to_him(): void
	{
		$user = User::factory()->create();

	    $restaurant = Restaurant::factory()->create();

	    $deliveryMan = DeliveryMan::create([
		                'name' => 'Rahim', 
		                'email' => 'rahim@gmail.com',
		                'password' => Hash::make('1234'),
		                'latitude' => 23.7810, 
		                'longitude' => 90.2795, 
		                'is_available' => true
		            ]);

	    $otherDeliveryMan = DeliveryMan::create([
		                'name' => 'Jalal', 
		                'email' => 'jalal@gmail.com',
		                'password' => Hash::make('12345'),
		                'latitude' => 23.8810, 
		                'longitude' => 90.3795, 
		                'is_available' => true
		            ]);

		config(['sanctum.guard' => ['web', 'delivery_man', 'admin']]);
	    
	    Sanctum::actingAs($deliveryMan, [], 'delivery_man');

	    $order = Order::create([
	        'user_id' => $user->id,
	        'restaurant_id' => $restaurant->id,
	        'delivery_men_id' => $otherDeliveryMan->id,
	        'status' => Order::PENDING,
	        'delivery_lat' => 23.7811,
	        'delivery_lng' => 90.2796,
	        'total_amount' => 150,
	    ]);

	    $payload = [
		        'order_id' => $order->id,
		        'status' => Order::APPROVED, 
		    ];

	    $response = $this->postJson('/api/delivery/orders/status', $payload);

	    $response->assertStatus(422)
	    		->assertJsonFragment([
			        'message' => 'Validation errors!',
			        'status' => 'FAILED',
			    ]);

	    $response->assertJsonPath('data.user_id.0', 'You have not been assigned to deliver this order.');

	    $this->assertDatabaseHas('orders', [
	        'id' => $order->id,
	        'status' => Order::PENDING,
	    ]);
	}
}
