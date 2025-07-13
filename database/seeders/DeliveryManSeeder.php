<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Seeder;
use App\Models\DeliveryMan;

class DeliveryManSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DeliveryMan::query()->truncate();

        $data = [
            [
                'name' => 'Rahim', 
                'email' => 'rahim@gmail.com',
                'password' => Hash::make('1234'),
                'latitude' => 23.8115, 
                'longitude' => 90.4140, 
                'is_available' => true
            ],
            [
                'name' => 'Karim', 
                'email' => 'karim@gmail.com',
                'password' => Hash::make('12345'),
                'latitude' => 23.8075, 
                'longitude' => 90.4135, 
                'is_available' => true
            ],
            [
                'name' => 'Jalal', 
                'email' => 'jalal@gmail.com',
                'password' => Hash::make('12346'),
                'latitude' => 23.8040, 
                'longitude' => 90.4170, 
                'is_available' => false
            ],
        ];

		foreach ($data as $item) {
			DeliveryMan::create($item);
		}
    }
}
