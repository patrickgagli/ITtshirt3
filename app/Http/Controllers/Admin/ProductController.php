<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index(): View
    {
        $products = Product::query()
            ->with('category')
            ->orderBy('name')
            ->paginate(20);

        return view('admin.products.index', compact('products'));
    }

    public function create(): View
    {
        $categories = ProductCategory::orderBy('name')->get();

        return view('admin.products.create', compact('categories'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name'                => ['required', 'string', 'max:255'],
            'slug'                => ['required', 'string', 'max:255', 'unique:products,slug', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/'],
            'description'         => ['required', 'string'],
            'price'               => ['required', 'numeric', 'min:0.01'],
            'currency'            => ['required', 'string', 'size:3'],
            'is_featured'         => ['boolean'],
            'image_path'          => ['nullable', 'string', 'max:500'],
            'product_category_id' => ['nullable', 'integer', 'exists:product_categories,id'],
            'track_stock'         => ['boolean'],
            'stock_quantity'      => ['required_if:track_stock,1', 'integer', 'min:0'],
        ]);

        Product::create([
            'name'                => $validated['name'],
            'slug'                => $validated['slug'],
            'description'         => $validated['description'],
            'price_cents'         => (int) round(floatval($validated['price']) * 100),
            'currency'            => strtoupper($validated['currency']),
            'is_featured'         => $request->boolean('is_featured'),
            'image_path'          => $validated['image_path'] ?? null,
            'product_category_id' => $validated['product_category_id'] ?? null,
            'track_stock'         => $request->boolean('track_stock'),
            'stock_quantity'      => $request->boolean('track_stock') ? (int) ($validated['stock_quantity'] ?? 0) : 0,
        ]);

        return redirect()->route('admin.products.index')
            ->with('success', "Produit « {$validated['name']} » créé avec succès.");
    }

    public function edit(Product $product): View
    {
        $categories = ProductCategory::orderBy('name')->get();

        return view('admin.products.edit', compact('product', 'categories'));
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        $validated = $request->validate([
            'name'                => ['required', 'string', 'max:255'],
            'slug'                => ['required', 'string', 'max:255', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/', "unique:products,slug,{$product->id}"],
            'description'         => ['required', 'string'],
            'price'               => ['required', 'numeric', 'min:0.01'],
            'currency'            => ['required', 'string', 'size:3'],
            'is_featured'         => ['boolean'],
            'image_path'          => ['nullable', 'string', 'max:500'],
            'product_category_id' => ['nullable', 'integer', 'exists:product_categories,id'],
            'track_stock'         => ['boolean'],
            'stock_quantity'      => ['required_if:track_stock,1', 'integer', 'min:0'],
        ]);

        $product->update([
            'name'                => $validated['name'],
            'slug'                => $validated['slug'],
            'description'         => $validated['description'],
            'price_cents'         => (int) round(floatval($validated['price']) * 100),
            'currency'            => strtoupper($validated['currency']),
            'is_featured'         => $request->boolean('is_featured'),
            'image_path'          => $validated['image_path'] ?? null,
            'product_category_id' => $validated['product_category_id'] ?? null,
            'track_stock'         => $request->boolean('track_stock'),
            'stock_quantity'      => $request->boolean('track_stock') ? (int) ($validated['stock_quantity'] ?? 0) : 0,
        ]);

        return redirect()->route('admin.products.index')
            ->with('success', "Produit « {$validated['name']} » mis à jour.");
    }

    public function destroy(Product $product): RedirectResponse
    {
        $name = $product->name;
        $product->delete();

        return redirect()->route('admin.products.index')
            ->with('success', "Produit « {$name} » supprimé.");
    }

    public static function suggestSlug(string $name, ?int $excludeId = null): string
    {
        $base = Str::slug($name);
        $slug = $base;
        $i    = 2;

        while (
            Product::where('slug', $slug)
                ->when($excludeId, fn ($q) => $q->where('id', '!=', $excludeId))
                ->exists()
        ) {
            $slug = $base . '-' . $i++;
        }

        return $slug;
    }
}
