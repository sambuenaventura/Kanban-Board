<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Laravel\Cashier\Exceptions\IncompletePayment;

class CheckoutController extends Controller
{
    public function __invoke(Request $request, string $plan = 'price_1Q93jFAtSEuPnXfe12HX00Xl')
    {
        $user = $request->user();
        // If the user already has lifetime access, redirect them
        if ($user->hasLifetimeAccess()) {
            return redirect()->route('pricing.index')->with('info', 'You already have lifetime access.');
        }
        // Handle lifetime plan separately
        if ($plan === 'price_1Q93kUAtSEuPnXfeIJNqhbqM') {
            return $user->checkoutCharge(280000, 'Lifetime Access', 1, [
                'success_url' => route('subscription.handle-lifetime'),
                'cancel_url' => route('pricing.index'),
            ]);
        }
        // For other plans, proceed with subscription checkout
        try {
            return $user
                ->newSubscription('prod_R15v1tLN1697qM', $plan)
                ->checkout([
                    'success_url' => route('subscription.handle-success', ['plan' => $plan]),
                    'cancel_url' => route('pricing.index'),
                ]);
        } catch (IncompletePayment $exception) {
            return redirect()->route(
                'cashier.payment',
                [$exception->payment->id, 'redirect' => route('pricing.index')]
            );
        }
    }
}