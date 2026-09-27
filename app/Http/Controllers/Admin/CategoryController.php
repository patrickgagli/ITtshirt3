<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProductCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function index(): View
    {
        $categories = ProductCategory::withCount('products')
            ->orderBy('name')
            ->get();

        return view('admin.categories.index', compact('categories'));
    }

    public function create(): View
    {
        return view('admin.categories.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name'        => ['required', 'string', 'max:255'],
            'slug'        => ['required', 'string', 'max:255', 'unique:product_categories,slug', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/'],
            'description' => ['nullable', 'string', 'max:1000'],
        ]);

        ProductCategory::create($validated);

        return redirect()->route('admin.categories.index')
            ->with('success', "Catégorie « {$validated['name']} » créée avec succès.");
    }

    public function edit(ProductCategory $category): View
    {
        return view('admin.categories.edit', compact('category'));
    }

    public function update(Request $request, ProductCategory $category): RedirectResponse
    {
        $validated = $request->validate([
            'name'        => ['required', 'string', 'max:255'],
            'slug'        => ['required', 'string', 'max:255', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/', "unique:product_categories,slug,{$category->id}"],
            'description' => ['nullable', 'string', 'max:1000'],
        ]);

        $category->update($validated);

        return redirect()->route('admin.categories.index')
            ->with('success', "Catégorie « {$validated['name']} » mise à jour.");
    }

    public function destroy(ProductCategory $category): RedirectResponse
    {
        $name = $category->name;
        $category->delete();

        return redirect()->route('admin.categories.index')
            ->with('success', "Catégorie « {$name} » supprimée. Les produits associés ont été désliés.");
    }

    public static function suggestSlug(string $name, ?int $excludeId = null): string
    {
        $base = Str::slug($name);
        $slug = $base;
        $i    = 2;

        while (
            ProductCategory::where('slug', $slug)
                ->when($excludeId, fn ($q) => $q->where('id', '!=', $excludeId))
                ->exists()
        ) {
            $slug = $base . '-' . $i++;
        }

        return $slug;
    }
}
