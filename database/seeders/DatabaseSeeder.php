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
        // //User::factory(10)->create();
        // //faster seed php -d memory_limit=-1 artisan db:seed
        // //php -d memory_limit=1G artisan db:seed --class=WorldDataSeeder 

        // //Clear existing users
        // User::truncate();
        
        // User::factory()->create([
        //     'name' => 'jak',
        //     'email' => 'jak@example.com',
        //     'password' => bcrypt('123456'),
        //     'email_verified_at' => now(),
        //     'remember_token' => null,
        //     'created_at' => now(),
        //     'updated_at' => now(),
        // ]);

        //PRODUCTION SEEDER
        // $this->call(ProductSeeder::class);
        // $this->call(CategorySeeder::class);
        // $this->call(ProfessionalCouponsSeeder::class);
        $this->call(WorldDataSeeder::class);
        // $this->call(SliderSeeder::class);
        
        // // Test orders for testing order tracking functionality
        // $this->call(OrderTestSeeder::class);
    }
}
