<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        $name = fake()->unique()->words(3, true);

        return [
            'name'           => Str::title($name),
            'slug'           => Str::slug($name) . '-' . fake()->unique()->numberBetween(10, 99),
            'description'    => fake()->sentence(18),
            'price_cents'    => fake()->numberBetween(2200, 4200),
            'currency'       => 'EUR',
            'is_featured'    => false,
            'track_stock'    => false,
            'stock_quantity' => 0,
        ];
    }

    public function featured(): static
    {
        return $this->state(fn () => [
            'is_featured' => true,
        ]);
    }

    /** Product with stock tracking enabled and plenty of stock. */
    public function inStock(int $quantity = 20): static
    {
        return $this->state(fn () => [
            'track_stock'    => true,
            'stock_quantity' => $quantity,
        ]);
    }

    /** Product with tracked stock at or below the low-stock threshold. */
    public function lowStock(int $quantity = 3): static
    {
        return $this->state(fn () => [
            'track_stock'    => true,
            'stock_quantity' => $quantity,
        ]);
    }

    /** Product with tracked stock exhausted. */
    public function outOfStock(): static
    {
        return $this->state(fn () => [
            'track_stock'    => true,
            'stock_quantity' => 0,
        ]);
    }
}
