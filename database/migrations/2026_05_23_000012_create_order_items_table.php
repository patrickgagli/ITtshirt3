<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            // Nullable so the row survives if the original product is later deleted.
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            // Snapshot fields so the order is self-contained regardless of catalogue changes.
            $table->string('product_name');
            $table->unsignedInteger('unit_price_cents');
            $table->unsignedSmallInteger('quantity');
            $table->string('currency', 3)->default('EUR');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};
