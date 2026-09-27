@extends('admin.layout')

@section('title', 'Commandes')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h1 class="page-heading fs-3 mb-0">Commandes</h1>
        <p class="text-muted mb-0 small">Gérez et traitez toutes les commandes clients.</p>
    </div>
</div>

{{-- Status filter tabs --}}
<div class="mb-4 d-flex flex-wrap gap-2">
    <a href="{{ route('admin.orders.index') }}"
       class="btn btn-sm {{ !request('status') ? 'btn-primary' : 'btn-outline-secondary' }}">
        Toutes <span class="badge bg-white text-dark ms-1">{{ $counts->sum() }}</span>
    </a>
    @foreach(\App\Models\Order::allowedStatuses() as $s)
    <a href="{{ route('admin.orders.index', ['status' => $s]) }}"
       class="btn btn-sm {{ request('status') === $s ? 'btn-primary' : 'btn-outline-secondary' }}">
        {{ (new \App\Models\Order(['status' => $s]))->statusLabel() }}
        @if($counts->has($s))<span class="badge bg-white text-dark ms-1">{{ $counts[$s] }}</span>@endif
    </a>
    @endforeach
</div>

@if($orders->isEmpty())
<div class="card p-5 text-center">
    <i class="fa-solid fa-bag-shopping fa-3x text-muted opacity-25 mb-3 d-block"></i>
    <p class="text-muted mb-0">Aucune commande{{ request('status') ? ' avec ce statut' : '' }}.</p>
</div>
@else
<div class="card">
    <table class="table admin-table mb-0">
        <thead>
            <tr>
                <th>Référence</th>
                <th>Date</th>
                <th>Client</th>
                <th>Statut</th>
                <th class="text-end">Total</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @foreach($orders as $order)
            <tr>
                <td>
                    <a href="{{ route('admin.orders.show', $order) }}" class="text-decoration-none fw-semibold">
                        {{ $order->order_number ?? strtoupper(substr($order->uuid, 0, 8)) }}
                    </a>
                </td>
                <td class="text-muted small">{{ $order->created_at->format('d/m/Y H:i') }}</td>
                <td>
                    @if($order->user)
                        <a href="#" class="text-decoration-none small">{{ $order->user->name }}</a><br>
                        <span class="text-muted" style="font-size:0.75rem;">{{ $order->email }}</span>
                    @else
                        <span class="small text-muted">Invité</span><br>
                        <span class="text-muted" style="font-size:0.75rem;">{{ $order->email }}</span>
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
</div>

<div class="mt-4">
    {{ $orders->links() }}
</div>
@endif
@endsection
