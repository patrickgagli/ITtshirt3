<?php

use App\Models\ProductCategory;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('image_path')->nullable()->after('is_featured');
            $table->foreignIdFor(ProductCategory::class)->nullable()->after('image_path')->constrained()->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropForeignIdFor(ProductCategory::class);
            $table->dropColumn('image_path');
        });
    }
};
