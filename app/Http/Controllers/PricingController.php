<?php

namespace App\Http\Controllers;

use App\Services\PlanService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Cashier\Cashier;

class PricingController extends Controller
{
    private $planService;

    public function __construct(PlanService $planService)
    {
        $this->planService = $planService;
    }

    public function showPricing()
    {
        $plans = $this->planService->getPricingPlans();
        
        $user = auth()->user();
        
        $currentSubscription = null;
    
        // Check if the user has an active Premium subscription
        if ($user->subscribed('premium_subscription_monthly') || $user->subscribed('premium_subscription_yearly')) {
            $currentSubscription = 'Premium'; // Set to 'Premium' if they have any Premium subscription
        } elseif ($user->subscribed('premium_plus_subscription_monthly') || $user->subscribed('premium_plus_subscription_yearly')) {
            $currentSubscription = 'Premium+'; // Set to 'Premium+' if they have any Premium+ subscription
        } else {
            $currentSubscription = 'Basic'; // Default if the user has no subscriptions
        }
        
        return view('pricing.index', compact('plans', 'currentSubscription'));
    }
    
    public function selectBillingPeriod($plan)
    {
        $plans = $this->planService->getPricingPlans();
        $selectedPlan = collect($plans)->firstWhere('slug', $plan);

        if (!$selectedPlan) {
            abort(404);
        }

        return view('pricing.billing', [
            'plan' => $plan,
            'selectedPlan' => $selectedPlan,
        ]);
    }
}