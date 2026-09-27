<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class AccountController extends Controller
{
    public function show(Request $request): View
    {
        $recentOrders = Order::where('user_id', $request->user()->id)
            ->latest()
            ->limit(5)
            ->get();

        return view('account.show', [
            'user'         => $request->user(),
            'recentOrders' => $recentOrders,
        ]);
    }

    public function orders(Request $request): View
    {
        $orders = Order::where('user_id', $request->user()->id)
            ->latest()
            ->paginate(15);

        return view('account.orders', compact('orders'));
    }

    public function showOrder(Request $request, Order $order): View
    {
        abort_unless($order->user_id === $request->user()->id, 403);

        $order->load('items');

        return view('account.order', compact('order'));
    }

    public function updateProfile(Request $request): RedirectResponse
    {
        $user = $request->user();

        $validated = $request->validateWithBag('profile', [
            'name'             => ['required', 'string', 'max:255'],
            'email'            => ['required', 'email', 'max:255', "unique:users,email,{$user->id}"],
            'current_password' => [
                'required',
                'string',
                function (string $attribute, mixed $value, \Closure $fail) use ($user) {
                    if (! Hash::check($value, $user->password)) {
                        $fail('Mot de passe actuel incorrect.');
                    }
                },
            ],
        ]);

        $user->update([
            'name'  => $validated['name'],
            'email' => $validated['email'],
        ]);

        return redirect()->route('account.show')->with('success_profile', 'Profil mis à jour avec succès.');
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $user = $request->user();

        $validated = $request->validateWithBag('password', [
            'current_password' => [
                'required',
                'string',
                function (string $attribute, mixed $value, \Closure $fail) use ($user) {
                    if (! Hash::check($value, $user->password)) {
                        $fail('Mot de passe actuel incorrect.');
                    }
                },
            ],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user->update(['password' => $validated['password']]);

        return redirect()->route('account.show')->with('success_password', 'Mot de passe mis à jour avec succès.');
    }
}
