@extends('admin.layout')

@section('title', 'Tableau de bord')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h1 class="page-heading fs-3 mb-0">Tableau de bord</h1>
        <p class="text-muted mb-0 small">Bienvenue, {{ auth()->user()->name }}.</p>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="card p-4">
            <span class="display-6 fw-bold text-primary">{{ $userCount }}</span>
            <span class="small text-uppercase text-muted fw-semibold">Utilisateurs</span>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card p-4">
            <span class="display-6 fw-bold text-danger">{{ $adminCount }}</span>
            <span class="small text-uppercase text-muted fw-semibold">Administrateurs</span>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card p-4">
            <span class="display-6 fw-bold text-primary">{{ $productCount }}</span>
            <span class="small text-uppercase text-muted fw-semibold">Produits</span>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card p-4">
            <span class="display-6 fw-bold text-primary">{{ $categoryCount }}</span>
            <span class="small text-uppercase text-muted fw-semibold">Catégories</span>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="card p-4">
            <span class="display-6 fw-bold text-success">{{ $orderCount }}</span>
            <span class="small text-uppercase text-muted fw-semibold">Commandes</span>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card p-4">
            <span class="display-6 fw-bold text-warning">{{ $pendingCount }}</span>
            <span class="small text-uppercase text-muted fw-semibold">À traiter</span>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card p-4">
            <span class="display-6 fw-bold text-success">{{ number_format($revenueTotal / 100, 2, ',', ' ') }} €</span>
            <span class="small text-uppercase text-muted fw-semibold">Chiffre d'affaires</span>
        </div>
    </div>
</div>

{{-- Stock alerts (only shown when there are tracked products in alert state) --}}
@if($outOfStockCount > 0 || $lowStockCount > 0)
<div class="row g-4 mb-4">
    @if($outOfStockCount > 0)
    <div class="col-sm-6 col-xl-3">
        <div class="card p-4 border-danger border-opacity-50">
            <span class="display-6 fw-bold text-danger">{{ $outOfStockCount }}</span>
            <span class="small text-uppercase text-muted fw-semibold">Rupture de stock</span>
            <a href="{{ route('admin.products.index') }}" class="btn btn-outline-danger btn-sm mt-2">Gérer</a>
        </div>
    </div>
    @endif
    @if($lowStockCount > 0)
    <div class="col-sm-6 col-xl-3">
        <div class="card p-4 border-warning border-opacity-50">
            <span class="display-6 fw-bold text-warning">{{ $lowStockCount }}</span>
            <span class="small text-uppercase text-muted fw-semibold">Stock faible</span>
            <a href="{{ route('admin.products.index') }}" class="btn btn-outline-warning btn-sm mt-2">Gérer</a>
        </div>
    </div>
    @endif
</div>
@endif

{{-- Analytics dashboard (Vue + Chart.js islands) --}}
<div class="row g-4 mb-4">
    <div class="col-lg-8">
        <div class="card p-4 h-100">
            <div data-vue="admin-sales-chart" data-props="{{ json_encode($analytics['sales']) }}"></div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card p-4 h-100">
            <div data-vue="admin-inventory-chart" data-props="{{ json_encode($analytics['inventory']) }}"></div>
        </div>
    </div>
    <div class="col-lg-7">
        <div class="card p-4 h-100">
            <div data-vue="admin-top-categories-chart" data-props="{{ json_encode($analytics['topCategories']) }}"></div>
        </div>
    </div>
    <div class="col-lg-5">
        <div class="card p-4 h-100">
            <div data-vue="admin-customer-insights-chart" data-props="{{ json_encode($analytics['customers']) }}"></div>
        </div>
    </div>
</div>

{{-- Contact requests chart --}}
<div class="row g-4 mb-4">
    <div class="col-12">
        <div class="card p-4">
            <div data-vue="admin-contact-requests-chart" data-props="{{ json_encode($analytics['contact']) }}"></div>
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-md-6">
        <div class="card p-4">
            <h5 class="fw-bold mb-3"><i class="fa-solid fa-shirt me-2 text-primary"></i>Produits</h5>
            <p class="text-muted small mb-3">Gérez le catalogue produits : créer, modifier, supprimer.</p>
            <div class="d-flex gap-2">
                <a href="{{ route('admin.products.index') }}" class="btn btn-primary btn-sm">Voir les produits</a>
                <a href="{{ route('admin.products.create') }}" class="btn btn-outline-primary btn-sm">+ Ajouter</a>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card p-4">
            <h5 class="fw-bold mb-3"><i class="fa-solid fa-tags me-2 text-primary"></i>Catégories</h5>
            <p class="text-muted small mb-3">Gérez les catégories du catalogue.</p>
            <div class="d-flex gap-2">
                <a href="{{ route('admin.categories.index') }}" class="btn btn-primary btn-sm">Voir les catégories</a>
                <a href="{{ route('admin.categories.create') }}" class="btn btn-outline-primary btn-sm">+ Ajouter</a>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card p-4">
            <h5 class="fw-bold mb-3"><i class="fa-solid fa-bag-shopping me-2 text-success"></i>Commandes</h5>
            <p class="text-muted small mb-3">Traitez et suivez les commandes clients.</p>
            <div class="d-flex gap-2">
                <a href="{{ route('admin.orders.index') }}" class="btn btn-success btn-sm">Voir les commandes</a>
                @if($pendingCount > 0)
                <a href="{{ route('admin.orders.index', ['status' => 'paid']) }}" class="btn btn-outline-warning btn-sm">{{ $pendingCount }} à traiter</a>
                @endif
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card p-4">
            <h5 class="fw-bold mb-3"><i class="fa-solid fa-sliders me-2 text-secondary"></i>Paramètres du site</h5>
            <p class="text-muted small mb-3">SEO, référencement et liens réseaux sociaux.</p>
            <div class="d-flex gap-2">
                <a href="{{ route('admin.settings.edit') }}" class="btn btn-secondary btn-sm">Modifier les paramètres</a>
            </div>
        </div>
    </div>
</div>

{{-- Recent orders --}}
<div class="card p-4 mt-4">
    <div class="d-flex align-items-center justify-content-between mb-3">
        <h5 class="fw-bold mb-0"><i class="fa-solid fa-clock-rotate-left me-2 text-success"></i>Commandes récentes</h5>
        <a href="{{ route('admin.orders.index') }}" class="btn btn-outline-success btn-sm">Toutes les commandes</a>
    </div>
    @if($recentOrders->isEmpty())
    <p class="text-muted small mb-0">Aucune commande pour le moment.</p>
    @else
    <table class="table admin-table mb-0">
        <thead>
            <tr>
                <th>Référence</th>
                <th>Date</th>
                <th>Client</th>
                <th>Paiement</th>
                <th>Livraison</th>
                <th class="text-end">Total</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @foreach($recentOrders as $order)
            <tr>
                <td>
                    <a href="{{ route('admin.orders.show', $order) }}" class="text-decoration-none fw-semibold">
                        {{ $order->order_number ?? strtoupper(substr($order->uuid, 0, 8)) }}
                    </a>
                </td>
                <td class="text-muted small">{{ $order->created_at->format('d/m/Y H:i') }}</td>
                <td class="small">
                    {{ $order->user?->name ?? 'Invité' }}<br>
                    <span class="text-muted" style="font-size:0.75rem;">{{ $order->email }}</span>
                </td>
                <td>
                    @if($order->paid_at)
                        <span class="badge bg-success" style="font-size:0.7rem;">Payée le {{ $order->paid_at->format('d/m/Y') }}</span>
                    @else
                        <span class="badge bg-warning text-dark" style="font-size:0.7rem;">En attente</span>
                    @endif
                </td>
                <td>
                    <span class="badge {{ $order->statusBadgeClass() }}" style="font-size:0.7rem;">{{ $order->statusLabel() }}</span>
                </td>
                <td class="text-end fw-semibold">{{ $order->formattedTotal() }}</td>
                <td class="text-end">
                    <a href="{{ route('admin.orders.show', $order) }}" class="btn btn-outline-primary btn-sm">Détails</a>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif
</div>
@endsection
