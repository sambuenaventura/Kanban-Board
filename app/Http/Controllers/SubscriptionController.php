<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Laravel\Cashier\Exceptions\IncompletePayment;

class SubscriptionController extends Controller
{
    public function change(Request $request)
    {
        $user = Auth::user();
        $newPlan = $request->input('plan');

        try {
            if ($user->hasLifetimeAccess()) {
                return redirect()->route('pricing.index')->with('info', 'You already have lifetime access.');
            }

            if ($newPlan === 'price_1Q93kUAtSEuPnXfeIJNqhbqM') {
                return redirect()->route('checkout', ['plan' => $newPlan]);
            }

            if ($user->subscription('prod_R15v1tLN1697qM')) {
                if ($newPlan !== 'free') {
                    $user->subscription('prod_R15v1tLN1697qM')->swap($newPlan);
                } else {
                    $user->subscription('prod_R15v1tLN1697qM')->cancel();
                }
            } else {
                return redirect()->route('checkout', ['plan' => $newPlan]);
            }
        } catch (IncompletePayment $exception) {
            return redirect()->route('cashier.payment', [$exception->payment->id, 'redirect' => route('pricing.index')]);
        } catch (\Exception $e) {
            Log::error('Subscription change error: ' . $e->getMessage());
            return redirect()->route('pricing.index')->with('error', 'There was an error changing your subscription. Please try again.');
        }

        return redirect()->route('pricing.index')->with('success', 'Subscription updated successfully!');
    }

    public function cancel(Request $request)
    {
        $user = Auth::user();
       
        try {
            if ($user->hasLifetimeAccess()) {
                return redirect()->route('pricing.index')->with('info', 'Lifetime access cannot be cancelled.');
            }

            if ($user->subscription('prod_R15v1tLN1697qM')) {
                $user->subscription('prod_R15v1tLN1697qM')->cancelNow();
            }
        } catch (\Exception $e) {
            Log::error('Subscription cancellation error: ' . $e->getMessage());
            return redirect()->route('pricing.index')->with('error', 'There was an error cancelling your subscription. Please try again.');
        }

        return redirect()->route('pricing.index')->with('success', 'Subscription cancelled successfully!');
    }

    public function handleLifetime()
    {
        $user = Auth::user();
        $user->grantLifetimeAccess();
        
        // Cancel any existing subscriptions
        if ($user->subscription('prod_R15v1tLN1697qM')) {
            $user->subscription('prod_R15v1tLN1697qM')->cancelNow();
        }

        return redirect()->route('pricing.index')->with('success', 'Congratulations! You now have lifetime access.');
    }
    
    public function handleSuccess(Request $request, $plan)
    {
        $planName = $plan === 'price_1Q93jFAtSEuPnXfebazUYa6U' ? 'Monthly' : 'Yearly';
        return redirect()->route('pricing.index')->with('success', "Congratulations! You've successfully subscribed to the {$planName} plan.");
    }
}