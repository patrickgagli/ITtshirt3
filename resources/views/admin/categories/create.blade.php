@extends('admin.layout')

@section('title', 'Nouvelle catégorie')

@section('content')
<div class="d-flex align-items-center gap-3 mb-4">
    <a href="{{ route('admin.categories.index') }}" class="btn btn-sm btn-outline-secondary">
        <i class="fa-solid fa-arrow-left"></i>
    </a>
    <h1 class="page-heading fs-3 mb-0">Nouvelle catégorie</h1>
</div>

<div class="row">
    <div class="col-lg-7">
        <form method="POST" action="{{ route('admin.categories.store') }}" novalidate>
            @csrf

            <div class="card p-4 mb-4">
                <h6 class="fw-bold text-uppercase text-muted small mb-3">Informations</h6>

                <div class="mb-3">
                    <label for="name" class="form-label fw-semibold">Nom <span class="text-danger">*</span></label>
                    <input type="text" id="name" name="name"
                           class="form-control @error('name') is-invalid @enderror"
                           value="{{ old('name') }}" required>
                    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="mb-3">
                    <label for="slug" class="form-label fw-semibold">Slug <span class="text-danger">*</span></label>
                    <input type="text" id="slug" name="slug"
                           class="form-control font-monospace @error('slug') is-invalid @enderror"
                           value="{{ old('slug') }}" required
                           pattern="^[a-z0-9]+(?:-[a-z0-9]+)*$"
                           placeholder="ex: ma-categorie">
                    <div class="form-text">Minuscules, chiffres et tirets uniquement.</div>
                    @error('slug')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="mb-0">
                    <label for="description" class="form-label fw-semibold">Description</label>
                    <textarea id="description" name="description" rows="3"
                              class="form-control @error('description') is-invalid @enderror"
                              placeholder="Description courte de la catégorie…">{{ old('description') }}</textarea>
                    @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="fa-solid fa-plus me-1"></i>Créer la catégorie
                </button>
                <a href="{{ route('admin.categories.index') }}" class="btn btn-outline-secondary">Annuler</a>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const nameInput = document.getElementById('name');
    const slugInput = document.getElementById('slug');
    let slugEdited = slugInput.value.length > 0;

    nameInput.addEventListener('input', () => {
        if (!slugEdited) {
            slugInput.value = nameInput.value
                .toLowerCase()
                .normalize('NFD').replace(/[\u0300-\u036f]/g, '')
                .replace(/[^a-z0-9\s-]/g, '')
                .trim()
                .replace(/[\s_]+/g, '-')
                .replace(/-+/g, '-');
        }
    });

    slugInput.addEventListener('input', () => { slugEdited = slugInput.value.length > 0; });
</script>
@endpush
