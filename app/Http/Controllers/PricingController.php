<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PricingController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $subscription = $user ? $user->subscription('prod_R15v1tLN1697qM') : null;
        $currentPlan = $subscription && $subscription->active() ? $subscription->stripe_price : 'free';

        // Get the plans
        $plans = $this->getSubscriptionPlans($user);

        return view('pricing.index', compact('user', 'subscription', 'currentPlan', 'plans'));
    }

    protected function getSubscriptionPlans($user)
    {
        $lifetimeAccess = $user ? $user->hasLifetimeAccess() : false;

        return [
            [
                'name' => 'Free',
                'price' => '₱0',
                'period' => '/forever',
                'features' => ['Up to 3 boards', '30 tasks per board', 'Basic collaboration'],
                'action' => 'free',
                'button_text' => 'Downgrade to Free',
                'disabled' => $lifetimeAccess || ($user && ($user->hasPremiumAccess() || $user->hasYearlyAccess())),
            ],
            [
                'name' => 'Monthly',
                'price' => '₱90',
                'period' => '/month',
                'features' => ['Unlimited boards', 'Unlimited tasks', 'Advanced features'],
                'action' => 'price_1Q93jFAtSEuPnXfebazUYa6U',
                'button_text' => 'Get Monthly Access',
                'disabled' => $lifetimeAccess,
            ],
            [
                'name' => 'Yearly',
                'price' => '₱1,080',
                'period' => '/year',
                'features' => ['All Monthly features', 'Save 30%', 'Priority support'],
                'action' => 'price_1Q93jFAtSEuPnXfe12HX00Xl',
                'button_text' => 'Get Yearly Access',
                'popular' => true,
                'disabled' => $lifetimeAccess,
            ],
            [
                'name' => 'Lifetime',
                'price' => '₱2,800',
                'period' => '/once',
                'features' => ['All Yearly features', 'One-time payment', 'Future updates included'],
                'action' => 'price_1Q93kUAtSEuPnXfeIJNqhbqM',
                'button_text' => 'Get Lifetime Access',
                'disabled' => $lifetimeAccess,
            ],
        ];
    }
}