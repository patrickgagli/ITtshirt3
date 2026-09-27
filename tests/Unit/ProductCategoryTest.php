<?php

namespace Tests\Unit;

use App\Models\Product;
use App\Models\ProductCategory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductCategoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_product_category_has_many_products_relation(): void
    {
        $category = new ProductCategory();

        $this->assertInstanceOf(HasMany::class, $category->products());
    }

    public function test_product_belongs_to_category_relation(): void
    {
        $product = new Product();

        $this->assertInstanceOf(BelongsTo::class, $product->category());
    }
}
