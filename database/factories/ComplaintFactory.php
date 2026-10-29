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
        $category = fake()->randomElement(['civil', 'criminal_case']);

        return [
            'reference' => 'BRGY-'.now()->format('ymd').'-'.Str::upper(Str::random(6)),
            'resident_id' => User::factory(),
            'category' => $category,
            'title' => fake()->sentence(5),
            'respondents' => fake()->name(),
            'case_subject' => fake()->words(3, true),
            'incident_date' => $category === 'criminal_case'
                ? fake()->dateTimeBetween('-1 year', 'now')->format('Y-m-d')
                : null,
            'description' => fake()->paragraph(),
            'relief_sought' => $category === 'civil' ? fake()->sentence() : null,
            'witnesses' => $category === 'criminal_case' ? fake()->sentence() : null,
            'location' => fake()->streetAddress(),
            'status' => 'pending',
        ];
    }
}
