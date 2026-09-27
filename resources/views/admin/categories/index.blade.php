@extends('admin.layout')

@section('title', 'Catégories')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h1 class="page-heading fs-3 mb-0">Catégories</h1>
        <p class="text-muted small mb-0">{{ $categories->count() }} catégorie(s) au total.</p>
    </div>
    <a href="{{ route('admin.categories.create') }}" class="btn btn-primary">
        <i class="fa-solid fa-plus me-1"></i>Nouvelle catégorie
    </a>
</div>

<div class="card">
    <div class="card-body p-0">
        @if ($categories->isEmpty())
        <div class="text-center py-5 text-muted">
            <i class="fa-solid fa-tags fa-2x mb-3 opacity-25"></i>
            <p class="mb-0">Aucune catégorie. <a href="{{ route('admin.categories.create') }}">Créer la première</a>.</p>
        </div>
        @else
        <div class="table-responsive">
            <table class="table admin-table mb-0">
                <thead>
                    <tr>
                        <th class="ps-4">Nom</th>
                        <th>Slug</th>
                        <th>Description</th>
                        <th class="text-center">Produits</th>
                        <th class="text-end pe-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($categories as $category)
                    <tr>
                        <td class="ps-4 fw-semibold">{{ $category->name }}</td>
                        <td class="text-muted small font-monospace">{{ $category->slug }}</td>
                        <td class="text-muted small" style="max-width: 300px;">
                            {{ $category->description ? Str::limit($category->description, 80) : '—' }}
                        </td>
                        <td class="text-center">
                            <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25">
                                {{ $category->products_count }}
                            </span>
                        </td>
                        <td class="text-end pe-4">
                            <div class="d-flex gap-2 justify-content-end">
                                <a href="{{ route('admin.categories.edit', $category) }}"
                                   class="btn btn-sm btn-outline-primary">
                                    <i class="fa-solid fa-pen"></i>
                                </a>
                                <form method="POST"
                                      action="{{ route('admin.categories.destroy', $category) }}"
                                      data-confirm="Supprimer \u00ab {{ $category->name }} \u00bb ? Les produits liés seront désassociés."
                                      onsubmit="return confirm(this.dataset.confirm)">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger"
                                            {{ $category->products_count > 0 ? 'title="Cette catégorie contient des produits"' : '' }}>
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
        @endif
    </div>
</div>
@endsection
