<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductCategory;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __invoke(): View
    {
        return view('home', [
            'productCount' => Product::count(),
            'categoryCount' => ProductCategory::count(),
            'categories' => ProductCategory::withCount('products')->get(),
            'featuredProducts' => Product::with('category')->where('is_featured', true)->orderBy('name')->take(4)->get(),
        ]);
    }
}
