<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function index(Request $request): View
    {
        $query = Order::with('user')->latest();

        // Optional status filter from query string
        if ($request->filled('status') && in_array($request->status, Order::allowedStatuses(), true)) {
            $query->where('status', $request->status);
        }

        $orders  = $query->paginate(25)->withQueryString();
        $counts  = Order::selectRaw('status, count(*) as total')
                        ->groupBy('status')
                        ->pluck('total', 'status');

        return view('admin.orders.index', compact('orders', 'counts'));
    }

    public function show(Order $order): View
    {
        $order->load('items.product', 'user');

        return view('admin.orders.show', compact('order'));
    }

    public function updateStatus(Request $request, Order $order): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'string', 'in:' . implode(',', Order::allowedStatuses())],
        ]);

        $order->update(['status' => $validated['status']]);

        return redirect()->route('admin.orders.show', $order)
            ->with('success', 'Statut mis à jour : ' . $order->fresh()->statusLabel());
    }
}
