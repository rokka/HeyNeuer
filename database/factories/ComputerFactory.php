<?php

namespace Database\Factories;

use App\Enums\ComputerStatus;
use App\Enums\DeviceClass;
use App\Enums\DiskType;
use App\Models\Computer;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Computer>
 */
class ComputerFactory extends Factory
{
    public function definition(): array
    {
        return [
            'device_class' => fake()->randomElement(DeviceClass::cases())->value,
            'model'        => fake()->randomElement(['Dell Latitude 5520', 'Lenovo ThinkPad T480', 'HP EliteBook 840', 'Dell OptiPlex 7060']),
            'has_webcam'   => fake()->boolean(),
            'has_wifi'     => fake()->boolean(),
            'status'       => fake()->randomElement(ComputerStatus::cases())->value,
            'comment'      => fake()->optional()->sentence(),
            'cpu_model'    => fake()->randomElement(['Intel Core i5-8350U', 'Intel Core i7-8650U', 'AMD Ryzen 5 PRO 4650U']),
            'ram_gb'       => fake()->randomElement([4, 8, 16, 32]),
            'disk_type'    => fake()->randomElement(DiskType::cases())->value,
            'disk_gb'      => fake()->randomElement([128, 256, 512, 1000]),
        ];
    }
}
