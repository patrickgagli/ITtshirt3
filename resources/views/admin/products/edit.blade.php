@extends('admin.layout')

@section('title', 'Modifier — ' . $product->name)

@section('content')
<div class="d-flex align-items-center gap-3 mb-4">
    <a href="{{ route('admin.products.index') }}" class="btn btn-sm btn-outline-secondary">
        <i class="fa-solid fa-arrow-left"></i>
    </a>
    <div>
        <h1 class="page-heading fs-3 mb-0">Modifier le produit</h1>
        <p class="text-muted small mb-0">{{ $product->name }}</p>
    </div>
</div>

<form method="POST" action="{{ route('admin.products.update', $product) }}" novalidate>
    @csrf
    @method('PATCH')

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card p-4 mb-4">
                <h6 class="fw-bold text-uppercase text-muted small mb-3">Informations générales</h6>

                <div class="mb-3">
                    <label for="name" class="form-label fw-semibold">Nom <span class="text-danger">*</span></label>
                    <input type="text" id="name" name="name"
                           class="form-control @error('name') is-invalid @enderror"
                           value="{{ old('name', $product->name) }}" required>
                    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="mb-3">
                    <label for="slug" class="form-label fw-semibold">Slug <span class="text-danger">*</span></label>
                    <input type="text" id="slug" name="slug"
                           class="form-control font-monospace @error('slug') is-invalid @enderror"
                           value="{{ old('slug', $product->slug) }}" required
                           pattern="^[a-z0-9]+(?:-[a-z0-9]+)*$">
                    <div class="form-text">Modifier le slug changera l'URL publique du produit.</div>
                    @error('slug')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="mb-3">
                    <label for="description" class="form-label fw-semibold">Description <span class="text-danger">*</span></label>
                    <textarea id="description" name="description" rows="4"
                              class="form-control @error('description') is-invalid @enderror"
                              required>{{ old('description', $product->description) }}</textarea>
                    @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="card p-4">
                <h6 class="fw-bold text-uppercase text-muted small mb-3">Image</h6>
                <div class="mb-0">
                    <label for="image_path" class="form-label fw-semibold">URL ou chemin de l'image</label>
                    <input type="text" id="image_path" name="image_path"
                           class="form-control @error('image_path') is-invalid @enderror"
                           value="{{ old('image_path', $product->image_path) }}"
                           placeholder="https://exemple.com/image.jpg">
                    @error('image_path')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card p-4 mb-4">
                <h6 class="fw-bold text-uppercase text-muted small mb-3">Prix</h6>

                <div class="mb-3">
                    <label for="price" class="form-label fw-semibold">Prix <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <input type="number" id="price" name="price" step="0.01" min="0.01"
                               class="form-control @error('price') is-invalid @enderror"
                               value="{{ old('price', number_format($product->price_cents / 100, 2, '.', '')) }}"
                               required>
                        <input type="text" id="currency" name="currency" maxlength="3"
                               class="form-control text-uppercase @error('currency') is-invalid @enderror"
                               value="{{ old('currency', $product->currency) }}" style="max-width: 70px;">
                        @error('price')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        @error('currency')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>

            <div class="card p-4 mb-4">
                <h6 class="fw-bold text-uppercase text-muted small mb-3">Catégorie</h6>
                <div class="mb-0">
                    <label for="product_category_id" class="form-label fw-semibold">Catégorie</label>
                    <select id="product_category_id" name="product_category_id"
                            class="form-select @error('product_category_id') is-invalid @enderror">
                        <option value="">— Sans catégorie —</option>
                        @foreach ($categories as $cat)
                        <option value="{{ $cat->id }}"
                            {{ old('product_category_id', $product->product_category_id) == $cat->id ? 'selected' : '' }}>
                            {{ $cat->name }}
                        </option>
                        @endforeach
                    </select>
                    @error('product_category_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="card p-4 mb-4">
                <h6 class="fw-bold text-uppercase text-muted small mb-3">Options</h6>
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" id="is_featured" name="is_featured"
                           value="1" {{ old('is_featured', $product->is_featured) ? 'checked' : '' }}>
                    <label class="form-check-label fw-semibold" for="is_featured">Mettre à la une</label>
                </div>
            </div>

            <div class="card p-4 mb-4">
                <h6 class="fw-bold text-uppercase text-muted small mb-3">Gestion du stock</h6>
                <div class="form-check form-switch mb-3">
                    <input class="form-check-input" type="checkbox" id="track_stock" name="track_stock"
                           value="1" {{ old('track_stock', $product->track_stock) ? 'checked' : '' }}>
                    <label class="form-check-label fw-semibold" for="track_stock">Suivre le stock</label>
                    <div class="form-text">Si désactivé, le produit est toujours affiché comme disponible.</div>
                </div>
                <div id="stock_quantity_group" class="{{ old('track_stock', $product->track_stock) ? '' : 'd-none' }}">
                    <label for="stock_quantity" class="form-label fw-semibold">Quantité en stock</label>
                    <input type="number" id="stock_quantity" name="stock_quantity" min="0"
                           class="form-control @error('stock_quantity') is-invalid @enderror"
                           value="{{ old('stock_quantity', $product->stock_quantity) }}">
                    @error('stock_quantity')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="d-grid gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="fa-solid fa-floppy-disk me-1"></i>Enregistrer les modifications
                </button>
                <a href="{{ route('admin.products.index') }}" class="btn btn-outline-secondary">Annuler</a>
            </div>
        </div>
    </div>
</form>
@endsection

@push('scripts')
<script>
    const trackStockToggle = document.getElementById('track_stock');
    const stockQtyGroup    = document.getElementById('stock_quantity_group');

    trackStockToggle.addEventListener('change', () => {
        stockQtyGroup.classList.toggle('d-none', !trackStockToggle.checked);
    });
</script>
@endpush
