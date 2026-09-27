<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\ProductCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HomeTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_page_renders(): void
    {
        $this->get(route('home'))->assertOk();
    }

    public function test_featured_product_image_url_is_passed_to_showcase(): void
    {
        $category = ProductCategory::factory()->create();
        Product::factory()->featured()->create([
            'product_category_id' => $category->id,
            'image_path' => 'products/sample.jpg',
        ]);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('dynamic-product-showcase', false)
            ->assertSee(str_replace('/', '\/', asset('storage/products/sample.jpg')), false);
    }

    public function test_featured_product_without_image_passes_null_image_url(): void
    {
        $category = ProductCategory::factory()->create();
        Product::factory()->featured()->create([
            'product_category_id' => $category->id,
            'image_path' => null,
        ]);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('&quot;image_url&quot;:null', false);
    }
}
