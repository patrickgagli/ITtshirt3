<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Order>
 */
class OrderFactory extends Factory
{
    protected $model = Order::class;

    public function definition(): array
    {
        $subtotal = fake()->numberBetween(1000, 15000); // cents

        return [
            'uuid'                   => Str::uuid()->toString(),
            'user_id'                => null,
            'session_id'             => Str::uuid()->toString(),
            'status'                 => 'paid',
            'email'                  => fake()->safeEmail(),
            'phone'                  => fake()->optional(0.6)->numerify('06########'),
            'shipping_name'          => fake()->name(),
            'shipping_address_line1' => fake()->streetAddress(),
            'shipping_address_line2' => null,
            'shipping_city'          => fake()->city(),
            'shipping_postal_code'   => fake()->numerify('#####'),
            'shipping_country'       => 'FR',
            'subtotal_cents'         => $subtotal,
            'total_cents'            => $subtotal,
            'currency'               => 'EUR',
            'payment_method'         => 'card',
            'paid_at'                => now(),
        ];
    }

    /** Associate the order with an authenticated user. */
    public function forUser(User $user): static
    {
        return $this->state(fn () => [
            'user_id' => $user->id,
            'email'   => $user->email,
        ]);
    }

    /** Set order status. */
    public function status(string $status): static
    {
        return $this->state(fn () => [
            'status'  => $status,
            'paid_at' => in_array($status, ['paid', 'processing', 'shipped', 'delivered']) ? now() : null,
        ]);
    }

    public function pending(): static    { return $this->status('pending'); }
    public function paid(): static       { return $this->status('paid'); }
    public function processing(): static { return $this->status('processing'); }
    public function shipped(): static    { return $this->status('shipped'); }
    public function delivered(): static  { return $this->status('delivered'); }
    public function cancelled(): static  { return $this->status('cancelled'); }

    /** Generate an order_number for every factory-created order that lacks one. */
    public function configure(): static
    {
        return $this->afterCreating(function (Order $order) {
            if (! $order->order_number) {
                $order->update(['order_number' => Order::generateOrderNumber($order)]);
            }
        });
    }
}
