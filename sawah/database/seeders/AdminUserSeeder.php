<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder {
    public function run(): void {
        User::updateOrCreate(
            ['email' => 'admin@suwah.com'],
            [
                'name' => 'وليد',
                'password' => Hash::make('password'), 
                'is_admin' => true,
                'email_verified_at' => now(),
            ]
        );
    }
}
