<?php

namespace Database\Factories;

use App\Enums\LeadStatus;
use App\Enums\Niche;
use App\Models\Lead;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Lead>
 */
class LeadFactory extends Factory
{
    protected $model = Lead::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'company' => fake()->company(),
            'niche' => fake()->randomElement(Niche::cases()),
            'status' => LeadStatus::New,
            'score' => fake()->optional()->numberBetween(1, 5),
            'city' => fake()->city(),
            'state' => fake()->stateAbbr(),
            'country' => 'US',
            'website' => fake()->optional()->url(),
            'stack' => null,
            'stack_confirmed' => false,
            'signals' => null,
            'contact_name' => fake()->optional()->name(),
            'contact_role' => fake()->optional()->jobTitle(),
            'email' => fake()->optional()->companyEmail(),
            'phone' => fake()->optional()->phoneNumber(),
            'source' => fake()->randomElement(['Google Maps', 'Yelp', 'Directory']),
            'last_contacted_at' => null,
            'next_action' => null,
            'notes' => null,
        ];
    }
}
