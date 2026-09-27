@extends('admin.layout')

@section('title', 'Commande ' . ($order->order_number ?? strtoupper(substr($order->uuid, 0, 8))))

@section('content')

<nav aria-label="breadcrumb" class="mb-3">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}" class="text-decoration-none">Tableau de bord</a></li>
        <li class="breadcrumb-item"><a href="{{ route('admin.orders.index') }}" class="text-decoration-none">Commandes</a></li>
        <li class="breadcrumb-item active">{{ $order->order_number ?? strtoupper(substr($order->uuid, 0, 8)) }}</li>
    </ol>
</nav>

<div class="d-flex align-items-center gap-3 mb-4 flex-wrap">
    <h1 class="page-heading fs-3 mb-0">
        Commande <span style="font-family:monospace;">{{ $order->order_number ?? strtoupper(substr($order->uuid, 0, 8)) }}</span>
    </h1>
    <span class="badge {{ $order->statusBadgeClass() }} fs-6">{{ $order->statusLabel() }}</span>
    <span class="text-muted small ms-auto">Passée le {{ $order->created_at->format('d/m/Y à H:i') }}</span>
</div>

<div class="row g-4">

    {{-- Left column: items + update status --}}
    <div class="col-lg-7">

        {{-- Items --}}
        <div class="card p-4 mb-4">
            <h5 class="fw-bold mb-3"><i class="fa-solid fa-shirt me-2 text-primary"></i>Articles</h5>
            <table class="table admin-table mb-0">
                <thead>
                    <tr>
                        <th>Produit</th>
                        <th class="text-center">Qté</th>
                        <th class="text-end">PU</th>
                        <th class="text-end">Sous-total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($order->items as $item)
                    <tr>
                        <td>
                            @if($item->product)
                            <a href="{{ route('products.show', $item->product->slug) }}" target="_blank" class="text-decoration-none fw-semibold">
                                {{ $item->product_name }}
                            </a>
                            @else
                            <span class="fw-semibold text-muted">{{ $item->product_name }} <small>(supprimé)</small></span>
                            @endif
                        </td>
                        <td class="text-center">{{ $item->quantity }}</td>
                        <td class="text-end">{{ number_format($item->unit_price_cents / 100, 2, ',', ' ') }} €</td>
                        <td class="text-end fw-semibold">{{ number_format($item->lineTotalCents() / 100, 2, ',', ' ') }} €</td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="3" class="text-end fw-bold">Total</td>
                        <td class="text-end fw-bold text-primary">{{ $order->formattedTotal() }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>

        {{-- Update status --}}
        <div class="card p-4">
            <h5 class="fw-bold mb-3"><i class="fa-solid fa-arrows-rotate me-2 text-primary"></i>Mettre à jour le statut</h5>
            <form method="POST" action="{{ route('admin.orders.status', $order) }}" class="d-flex gap-2 align-items-end">
                @csrf
                @method('PATCH')
                <div class="flex-grow-1">
                    <label for="status" class="form-label fw-semibold small">Nouveau statut</label>
                    <select name="status" id="status" class="form-select @error('status') is-invalid @enderror">
                        @foreach(\App\Models\Order::allowedStatuses() as $s)
                        <option value="{{ $s }}" {{ $order->status === $s ? 'selected' : '' }}>
                            {{ (new \App\Models\Order(['status' => $s]))->statusLabel() }}
                        </option>
                        @endforeach
                    </select>
                    @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <button type="submit" class="btn btn-primary">
                    <i class="fa-solid fa-floppy-disk me-1"></i>Enregistrer
                </button>
            </form>
        </div>

    </div>

    {{-- Right column: customer + shipping --}}
    <div class="col-lg-5">

        {{-- Customer --}}
        <div class="card p-4 mb-4">
            <h5 class="fw-bold mb-3"><i class="fa-solid fa-user me-2 text-primary"></i>Client</h5>
            <div class="d-flex flex-column gap-2">
                @if($order->user)
                <div class="d-flex justify-content-between">
                    <span class="text-muted small">Nom</span>
                    <span class="fw-semibold small">{{ $order->user->name }}</span>
                </div>
                @endif
                <div class="d-flex justify-content-between">
                    <span class="text-muted small">E-mail</span>
                    <a href="mailto:{{ $order->email }}" class="fw-semibold small">{{ $order->email }}</a>
                </div>
                @if($order->phone)
                <div class="d-flex justify-content-between">
                    <span class="text-muted small">Téléphone</span>
                    <span class="fw-semibold small">{{ $order->phone }}</span>
                </div>
                @endif
                <div class="d-flex justify-content-between">
                    <span class="text-muted small">Type</span>
                    <span class="badge {{ $order->user ? 'bg-primary' : 'bg-secondary' }}" style="font-size:0.7rem;">
                        {{ $order->user ? 'Membre' : 'Invité' }}
                    </span>
                </div>
            </div>
        </div>

        {{-- Shipping --}}
        <div class="card p-4 mb-4">
            <h5 class="fw-bold mb-3"><i class="fa-solid fa-location-dot me-2 text-primary"></i>Livraison</h5>
            <address class="mb-0" style="font-style:normal;font-size:0.9rem;">
                <strong>{{ $order->shipping_name }}</strong><br>
                {{ $order->shipping_address_line1 }}
                @if($order->shipping_address_line2)<br>{{ $order->shipping_address_line2 }}@endif<br>
                {{ $order->shipping_postal_code }} {{ $order->shipping_city }}<br>
                {{ $order->shipping_country }}
            </address>
        </div>

        {{-- Payment --}}
        <div class="card p-4">
            <h5 class="fw-bold mb-3"><i class="fa-solid fa-credit-card me-2 text-primary"></i>Paiement</h5>
            <div class="d-flex flex-column gap-2">
                <div class="d-flex justify-content-between">
                    <span class="text-muted small">Méthode</span>
                    <span class="fw-semibold small">Carte bancaire</span>
                </div>
                @if($order->paid_at)
                <div class="d-flex justify-content-between">
                    <span class="text-muted small">Payé le</span>
                    <span class="fw-semibold small">{{ $order->paid_at->format('d/m/Y H:i') }}</span>
                </div>
                @endif
                <div class="d-flex justify-content-between">
                    <span class="text-muted small">Montant</span>
                    <span class="fw-bold text-primary">{{ $order->formattedTotal() }}</span>
                </div>
                @if($order->stripe_payment_intent_id)
                <div class="d-flex justify-content-between align-items-start gap-2">
                    <span class="text-muted small flex-shrink-0">Réf. Stripe</span>
                    <code class="small text-break text-end" style="font-size:0.7rem;">{{ $order->stripe_payment_intent_id }}</code>
                </div>
                @endif
            </div>
        </div>

    </div>
</div>

<div class="mt-4">
    <a href="{{ route('admin.orders.index') }}" class="btn btn-outline-secondary">
        <i class="fa-solid fa-arrow-left me-2"></i>Retour aux commandes
    </a>
</div>

@endsection
