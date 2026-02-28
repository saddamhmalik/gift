<?php

namespace Database\Seeders;

use App\Models\Admin;
use Illuminate\Database\Seeder;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        $email = 'admin@payflex.com';
        $password = bcrypt('Password@1');

        Admin::updateOrCreate(
            ['email' => $email],
            [
                'name' => 'Admin',
                'password' => $password,
            ]
        );
    }
}
