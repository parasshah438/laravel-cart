<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Database\Seeders\ProductSeeder;
class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        //User::factory(10)->create();

        User::factory()->create([
            'name' => 'jak',
            'email' => 'jak@example.com',
            'password' => bcrypt('password'),
            'email_verified_at' => now(),
            'remember_token' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        //PRODUCTION SEEDER
        $this->call(ProductSeeder::class);
        
    }
}
