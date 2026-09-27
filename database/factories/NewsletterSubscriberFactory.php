<?php

namespace Database\Factories;

use App\Models\NewsletterSubscriber;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<NewsletterSubscriber>
 */
class NewsletterSubscriberFactory extends Factory
{
    public function definition(): array
    {
        return [
            'email'           => fake()->unique()->safeEmail(),
            'name'            => fake()->name(),
            'token'           => NewsletterSubscriber::generateToken(),
            'subscribed_at'   => now(),
            'unsubscribed_at' => null,
        ];
    }

    public function unsubscribed(): static
    {
        return $this->state(fn () => ['unsubscribed_at' => now()]);
    }
}
