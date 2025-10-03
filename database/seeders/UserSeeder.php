<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::factory()->create([
                'name' => 'admin',
                'email' => 'admin@example.com',
                'role' => 'admin'
            ]);
        User::factory()->create([
                'name' => 'Divya',
                'email' => 'divya@example.com',
                'role' => 'manager'
            ]);
        User::factory()->create([
            'name' => 'Support',
            'email' => 'support@example.com',
            'role' => 'support'
        ]);
       User::factory()->count(10)->create();
    }
}
