<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Hash;
use Carbon;

class AdminUsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //\
        User::insert([
            [
                'id' => 1,
                'name' => 'Vijaya Kaspate',
                'email' => 'vijayakaspate@123gmail.com',
                'role' => 'admin',
                'email_verified_at' => null,
                'password' => Hash::make('vijaya@123'),
                'remember_token' => null,
                'created_at' => Carbon\Carbon::now(),
                'updated_at' => Carbon\Carbon::now(),
            ]
        ]);

    }
}
