<?php

namespace Database\Factories;

use App\Models\Member;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    /**
     * Put the account on the 교적, which is what makes it a 성도.
     *
     * Signing in is not what opens 성도 전용 content - being one of the
     * church's own is, and the roster record is the only thing that
     * says so. A plain factory account is a 일반회원: it can sign in and
     * read the public site, and nothing more.
     */
    public function onTheRoster(): static
    {
        return $this->afterCreating(fn (User $user) => Member::factory()->create([
            'name' => $user->name,
            'user_id' => $user->getKey(),
        ]));
    }
}
