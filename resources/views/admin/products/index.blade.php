@extends('admin.layout')

@section('title', 'Produits')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h1 class="page-heading fs-3 mb-0">Produits</h1>
        <p class="text-muted small mb-0">{{ $products->total() }} produit(s) au total.</p>
    </div>
    <a href="{{ route('admin.products.create') }}" class="btn btn-primary">
        <i class="fa-solid fa-plus me-1"></i>Nouveau produit
    </a>
</div>

<div class="card">
    <div class="card-body p-0">
        @if ($products->isEmpty())
        <div class="text-center py-5 text-muted">
            <i class="fa-solid fa-shirt fa-2x mb-3 opacity-25"></i>
            <p class="mb-0">Aucun produit. <a href="{{ route('admin.products.create') }}">Créer le premier</a>.</p>
        </div>
        @else
        <div class="table-responsive">
            <table class="table admin-table mb-0">
                <thead>
                    <tr>
                        <th class="ps-4">Nom</th>
                        <th>Slug</th>
                        <th>Catégorie</th>
                        <th>Prix</th>
                        <th class="text-center">Stock</th>
                        <th class="text-center">À la une</th>
                        <th class="text-end pe-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($products as $product)
                    <tr>
                        <td class="ps-4 fw-semibold">{{ $product->name }}</td>
                        <td class="text-muted small font-monospace">{{ $product->slug }}</td>
                        <td>
                            @if ($product->category)
                            <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25">
                                {{ $product->category->name }}
                            </span>
                            @else
                            <span class="text-muted small">—</span>
                            @endif
                        </td>
                        <td class="fw-semibold">{{ $product->formattedPrice() }}</td>
                        <td class="text-center">
                            @if($product->track_stock)
                                @if($product->isOutOfStock())
                                    <span class="badge bg-danger" style="font-size:0.7rem;">Rupture</span>
                                @elseif($product->isLowStock())
                                    <span class="badge bg-warning text-dark" style="font-size:0.7rem;">{{ $product->stock_quantity }} restant(s)</span>
                                @else
                                    <span class="badge bg-success" style="font-size:0.7rem;">{{ $product->stock_quantity }}</span>
                                @endif
                            @else
                                <span class="text-muted small">—</span>
                            @endif
                        </td>
                        <td class="text-center">
                            @if ($product->is_featured)
                            <span class="badge bg-warning text-dark"><i class="fa-solid fa-star me-1"></i>Oui</span>
                            @else
                            <span class="text-muted small">Non</span>
                            @endif
                        </td>
                        <td class="text-end pe-4">
                            <div class="d-flex gap-2 justify-content-end">
                                <a href="{{ route('products.show', $product->slug) }}"
                                   class="btn btn-sm btn-outline-secondary"
                                   target="_blank"
                                   title="Voir sur la vitrine">
                                    <i class="fa-solid fa-eye"></i>
                                </a>
                                <a href="{{ route('admin.products.edit', $product) }}"
                                   class="btn btn-sm btn-outline-primary">
                                    <i class="fa-solid fa-pen"></i>
                                </a>
                                <form method="POST"
                                      action="{{ route('admin.products.destroy', $product) }}"
                                      data-confirm="Supprimer \u00ab {{ $product->name }} \u00bb ? Cette action est irréversible."
                                      onsubmit="return confirm(this.dataset.confirm)">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @if ($products->hasPages())
        <div class="px-4 py-3 border-top">
            {{ $products->links() }}
        </div>
        @endif
        @endif
    </div>
</div>
@endsection
