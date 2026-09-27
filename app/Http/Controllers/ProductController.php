<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\WishlistItem;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductController extends Controller
{
    private const ALLOWED_SORTS = ['featured', 'name_asc', 'name_desc', 'price_asc', 'price_desc', 'stock'];
    private const ALLOWED_AVAILABILITY = ['all', 'in_stock', 'low_stock', 'out_of_stock'];
    private const PER_PAGE = 12;

    public function index(Request $request): View
    {
        $categories = ProductCategory::orderBy('name')->withCount('products')->get();

        $activeCategory = null;
        $search         = trim($request->input('q', ''));
        $sort           = in_array($request->input('sort'), self::ALLOWED_SORTS)
                            ? $request->input('sort')
                            : 'featured';
        $availability   = in_array($request->input('availability'), self::ALLOWED_AVAILABILITY)
                            ? $request->input('availability')
                            : 'all';

        $query = Product::query()->with('category');

        // Category filter
        if ($request->filled('category')) {
            $activeCategory = ProductCategory::where('slug', $request->input('category'))->firstOrFail();
            $query->where('product_category_id', $activeCategory->id);
        }

        // Text search across name and description
        if ($search !== '') {
            $query->where(function (Builder $q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                  ->orWhere('description', 'like', '%' . $search . '%');
            });
        }

        // Stock counts for filter pills — computed BEFORE the availability filter is applied.
        // 'in_stock' is an exclusive count: untracked products plus tracked products whose
        // stock exceeds the low-stock threshold (mirrors the three-way split all/in_stock/low_stock).
        $baseForCounts  = clone $query;
        $stockCounts = [
            'all'          => (clone $baseForCounts)->count(),
            'in_stock'     => (clone $baseForCounts)->where(function (Builder $q) {
                                  $q->where('track_stock', false)
                                    ->orWhere('stock_quantity', '>', Product::LOW_STOCK_THRESHOLD);
                              })->count(),
            'low_stock'    => (clone $baseForCounts)->lowStock()->count(),
            'out_of_stock' => (clone $baseForCounts)->outOfStock()->count(),
        ];

        // Stock availability filter — each bucket is mutually exclusive, matching the count badges:
        // in_stock = untracked OR stock_quantity > LOW_STOCK_THRESHOLD
        // low_stock = tracked, 0 < stock_quantity <= LOW_STOCK_THRESHOLD
        // out_of_stock = tracked, stock_quantity = 0
        match ($availability) {
            'in_stock'     => $query->where(function (Builder $q) {
                                  $q->where('track_stock', false)
                                    ->orWhere('stock_quantity', '>', Product::LOW_STOCK_THRESHOLD);
                              }),
            'low_stock'    => $query->lowStock(),
            'out_of_stock' => $query->outOfStock(),
            default        => null,
        };

        // Sort
        match ($sort) {
            'name_asc'   => $query->orderBy('name'),
            'name_desc'  => $query->orderByDesc('name'),
            'price_asc'  => $query->orderBy('price_cents')->orderBy('name'),
            'price_desc' => $query->orderByDesc('price_cents')->orderBy('name'),
            'stock'      => $query->orderByRaw('
                CASE
                    WHEN track_stock = 0 THEN 0
                    WHEN track_stock = 1 AND stock_quantity > ? THEN 1
                    WHEN track_stock = 1 AND stock_quantity > 0 THEN 2
                    ELSE 3
                END
            ', [Product::LOW_STOCK_THRESHOLD])->orderBy('name'),
            default      => $query->orderByDesc('is_featured')->orderBy('name'),
        };

        $products = $query->paginate(self::PER_PAGE)->withQueryString();

        return view('products.index', [
            'products'       => $products,
            'categories'     => $categories,
            'activeCategory' => $activeCategory,
            'search'         => $search,
            'sort'           => $sort,
            'availability'   => $availability,
            'stockCounts'    => $stockCounts,
        ]);
    }

    public function show(Request $request, string $slug): View
    {
        $product = Product::where('slug', $slug)->with('category')->firstOrFail();

        $wishlistItem = $request->user()
            ? WishlistItem::where('user_id', $request->user()->id)
                          ->where('product_id', $product->id)
                          ->first()
            : null;

        return view('products.show', [
            'product'      => $product,
            'wishlistItem' => $wishlistItem,
        ]);
    }
}
