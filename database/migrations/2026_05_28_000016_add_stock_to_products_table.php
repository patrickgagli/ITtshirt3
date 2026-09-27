<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // When track_stock is false the product is treated as always available.
            // When true, availability is driven by stock_quantity.
            $table->boolean('track_stock')->default(false)->after('is_featured');
            $table->unsignedInteger('stock_quantity')->default(0)->after('track_stock');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['track_stock', 'stock_quantity']);
        });
    }
};
