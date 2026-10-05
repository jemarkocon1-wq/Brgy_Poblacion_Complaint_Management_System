<?php

namespace Database\Factories;

use App\Models\Complaint;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Complaint>
 */
class ComplaintFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'reference' => 'BRGY-'.now()->format('ymd').'-'.Str::upper(Str::random(6)),
            'resident_id' => User::factory(),
            'category' => fake()->randomElement(['noise', 'sanitation', 'infrastructure', 'public_safety', 'other']),
            'title' => fake()->sentence(5),
            'description' => fake()->paragraph(),
            'location' => fake()->streetAddress(),
            'status' => 'pending',
        ];
    }
}
