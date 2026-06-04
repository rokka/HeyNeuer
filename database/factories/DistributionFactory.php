<?php

namespace Database\Factories;

use App\Models\Computer;
use App\Models\Distribution;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Distribution>
 */
class DistributionFactory extends Factory
{
    protected $model = Distribution::class;

    public function definition(): array
    {
        return [
            'computer_id'    => Computer::factory(),
            'user_id'        => User::factory(),
            'distributed_at' => fake()->dateTimeBetween('-3 months', 'now'),
            'recipient_hash' => hash('sha256', (string) fake()->unique()->uuid()),
        ];
    }
}
