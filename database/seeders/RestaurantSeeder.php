<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\DeliveryZone;
use App\Models\Restaurant;

class RestaurantSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Restaurant::query()->truncate();
        DeliveryZone::query()->truncate();

        Restaurant::factory(4)
                ->create()
                ->each(function ($restaurant, $index) {
                    if ($index % 2 == 0) {
                        DeliveryZone::create([
                            'restaurant_id' => $restaurant->id,
                            'type' => DeliveryZone::RADIUS,
                            'center_lat' => 23.8103,
                            'center_lng' => 90.4125,
                            'radius_km' => 5,
                        ]);
                    } else {
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
                    }
        });
    }
}
