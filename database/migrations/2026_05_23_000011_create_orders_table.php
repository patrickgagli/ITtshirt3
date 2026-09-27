<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('session_id', 64)->index();
            $table->string('status', 20)->default('pending'); // pending | paid | cancelled

            // Customer contact
            $table->string('email');
            $table->string('phone', 30)->nullable();

            // Shipping address (denormalised snapshot)
            $table->string('shipping_name');
            $table->string('shipping_address_line1');
            $table->string('shipping_address_line2')->nullable();
            $table->string('shipping_city');
            $table->string('shipping_postal_code', 20);
            $table->string('shipping_country', 2)->default('FR');

            // Financials
            $table->unsignedInteger('subtotal_cents');
            $table->unsignedInteger('total_cents');
            $table->string('currency', 3)->default('EUR');

            $table->string('payment_method', 20)->default('card');
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
