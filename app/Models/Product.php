<?php

namespace App\Models;

use Database\Factories\ProductFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'name',
    'slug',
    'description',
    'price_cents',
    'currency',
    'is_featured',
    'image_path',
    'product_category_id',
    'track_stock',
    'stock_quantity',
])]
class Product extends Model
{
    /** Low-stock alert threshold (inclusive). */
    public const LOW_STOCK_THRESHOLD = 5;

    /** @use HasFactory<ProductFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'is_featured'    => 'bool',
            'track_stock'    => 'bool',
            'stock_quantity' => 'int',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ProductCategory::class, 'product_category_id');
    }

    public function scopeCatalogued(Builder $query): Builder
    {
        return $query->orderByDesc('is_featured')->orderBy('name');
    }

    /** Scope: products that are currently in stock (or not tracked). */
    public function scopeInStock(Builder $query): Builder
    {
        return $query->where(function (Builder $q) {
            $q->where('track_stock', false)
              ->orWhere('stock_quantity', '>', 0);
        });
    }

    /** Scope: tracked products with stock at or below the low-stock threshold. */
    public function scopeLowStock(Builder $query, int $threshold = self::LOW_STOCK_THRESHOLD): Builder
    {
        return $query->where('track_stock', true)
                     ->where('stock_quantity', '>', 0)
                     ->where('stock_quantity', '<=', $threshold);
    }

    /** Scope: tracked products with zero stock. */
    public function scopeOutOfStock(Builder $query): Builder
    {
        return $query->where('track_stock', true)
                     ->where('stock_quantity', 0);
    }

    /** True when the product can be purchased (untracked = unlimited). */
    public function isInStock(): bool
    {
        return ! $this->track_stock || $this->stock_quantity > 0;
    }

    /** True when tracked stock is low but not yet zero. */
    public function isLowStock(): bool
    {
        return $this->track_stock
            && $this->stock_quantity > 0
            && $this->stock_quantity <= self::LOW_STOCK_THRESHOLD;
    }

    /** True when tracked stock is exhausted. */
    public function isOutOfStock(): bool
    {
        return $this->track_stock && $this->stock_quantity === 0;
    }

    /** Human-readable stock label. */
    public function stockLabel(): string
    {
        if (! $this->track_stock) {
            return 'Disponible';
        }

        if ($this->stock_quantity === 0) {
            return 'Rupture de stock';
        }

        if ($this->isLowStock()) {
            return 'Stock limité (' . $this->stock_quantity . ')';
        }

        return 'En stock (' . $this->stock_quantity . ')';
    }

    /** Bootstrap 5 badge/text class for the stock status. */
    public function stockBadgeClass(): string
    {
        if (! $this->track_stock) {
            return 'text-success';
        }

        if ($this->stock_quantity === 0) {
            return 'text-danger';
        }

        if ($this->isLowStock()) {
            return 'text-warning';
        }

        return 'text-success';
    }

    public function formattedPrice(): string
    {
        return number_format($this->price_cents / 100, 2, ',', ' ') . ' ' . $this->currency;
    }
}
