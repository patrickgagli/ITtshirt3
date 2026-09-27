<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

#[Fillable([
    'uuid',
    'order_number',
    'user_id',
    'session_id',
    'status',
    'email',
    'phone',
    'shipping_name',
    'shipping_address_line1',
    'shipping_address_line2',
    'shipping_city',
    'shipping_postal_code',
    'shipping_country',
    'subtotal_cents',
    'total_cents',
    'currency',
    'payment_method',
    'stripe_payment_intent_id',
    'paid_at',
])]
class Order extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'paid_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Order $order) {
            $order->uuid ??= Str::uuid()->toString();
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function formattedTotal(): string
    {
        return number_format($this->total_cents / 100, 2, ',', ' ') . ' ' . $this->currency;
    }

    /**
     * Generate a human-readable order number based on the order's database ID.
     * Format: ORD-YYYYMMDD-XXXXXX (e.g. ORD-20260528-000042)
     */
    public static function generateOrderNumber(self $order): string
    {
        return 'ORD-' . now()->format('Ymd') . '-' . str_pad((string) $order->id, 6, '0', STR_PAD_LEFT);
    }

    public function isPaid(): bool
    {
        return $this->status === 'paid';
    }

    /** Human-readable status label (French). */
    public function statusLabel(): string
    {
        return match ($this->status) {
            'pending'    => 'En attente',
            'paid'       => 'Payée',
            'processing' => 'En préparation',
            'shipped'    => 'Expédiée',
            'delivered'  => 'Livrée',
            'cancelled'  => 'Annulée',
            default      => ucfirst($this->status),
        };
    }

    /** Bootstrap 5 badge class for the current status. */
    public function statusBadgeClass(): string
    {
        return match ($this->status) {
            'pending'    => 'bg-warning text-dark',
            'paid'       => 'bg-primary',
            'processing' => 'bg-info',
            'shipped'    => 'bg-indigo text-white',
            'delivered'  => 'bg-success',
            'cancelled'  => 'bg-danger',
            default      => 'bg-secondary',
        };
    }

    /** Allowed status transitions that an admin may apply. */
    public static function allowedStatuses(): array
    {
        return ['pending', 'paid', 'processing', 'shipped', 'delivered', 'cancelled'];
    }
}
