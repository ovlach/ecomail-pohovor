<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ContactImport>
 */
class ContactImportFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id' => $this->faker->uuid(),
            'queue_at' => $this->faker->dateTime(),
            'started_at' => $this->faker->optional()->dateTime(),
            'finished_at' => $this->faker->optional()->dateTime(),
            'total_processed' => $this->faker->numberBetween(0, 1000),
            'errors' => $this->faker->numberBetween(0, 100),
            'duplicates' => $this->faker->numberBetween(0, 100),
            'file' => $this->faker->filePath(),
            'state' => $this->faker->randomElement(['PENDING', 'RUNNING', 'DONE']),
        ];
    }
}
