<?php

namespace Database\Seeders;

use App\Models\User;
use Faker\Factory as Faker;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            'first_name' => 'Super',
            'last_name' => 'Admin',
            'birth_date' => '1990-12-10',
            'gender' => 'male',
            'email' => 'superadmin@example.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);

        $faker = Faker::create();

        for ($i = 0; $i < 5; $i++) {
            User::create([
                'first_name' => $faker->firstName,
                'last_name' => $faker->lastName,
                'email' => $faker->email,
                'password' => Hash::make('password'),
                'role' => 'employee',
                'gender' => $faker->randomElement(['male', 'female']),
                'phone' => $faker->phoneNumber,
                'address' => $faker->address,
            ]);
        }
    }
}
