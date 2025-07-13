<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Seeder;
use App\Models\User;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::query()->truncate();

        $data = [
            [
                'name' => 'Al-Amin Sarker', 
                'email' => 'alamin@gmail.com',
                'password' => Hash::make('1234'),
            ],
            [
                'name' => 'Admin', 
                'email' => 'admin@gmail.com',
                'password' => Hash::make('12345'),
            ],
        ];

		foreach ($data as $item) {
			User::create($item);
		}
    }
}
