<?php

namespace Database\Seeders;

use App\Models\Sequence;
use App\Models\User;
use App\Services\ComputerNumberGenerator;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        Sequence::firstOrCreate(
            ['name' => ComputerNumberGenerator::SEQUENCE_NAME],
            ['value' => 0]
        );

        User::firstOrCreate(
            ['email' => env('SEED_ADMIN_EMAIL', 'admin@heyneuer.com')],
            [
                'name'          => 'Admin Hey-Alter',
                'password'      => Hash::make(env('SEED_ADMIN_PASSWORD', 'changeme!')),
                'is_admin'      => true,
                'registered_at' => now(),
            ]
        );
    }
}
