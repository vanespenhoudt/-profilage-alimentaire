<?php

namespace Database\Factories;

use App\Enums\Role;
use App\Models\Client;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Client>
 */
class ClientFactory extends Factory
{
    protected $model = Client::class;

    public function definition(): array
    {
        return [
            'conseiller_id' => User::factory()->state(['role' => Role::Conseiller->value, 'active' => true]),
            'prenom'        => fake()->firstName(),
            'nom'           => fake()->lastName(),
            'tel'           => fake()->phoneNumber(),
            'email'         => fake()->unique()->safeEmail(),
            'rgpd'          => true,
        ];
    }
}
