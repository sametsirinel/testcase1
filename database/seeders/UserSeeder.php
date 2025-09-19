<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
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
            "name"      => "User",
            "email"     => "user@example.com",
            "password"  => Hash::make("password"), // Hash halide yazılabilir
        ]);

        User::create([
            "name"      => "User 2",
            "email"     => "user2@example.com",
            "password"  => Hash::make("password"),
        ]);
    }
}
