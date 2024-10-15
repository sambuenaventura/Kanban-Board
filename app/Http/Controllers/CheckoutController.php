<?php

namespace App\Http\Controllers;

use App\Services\PlanService;
use App\Services\SubscriptionService;
use Illuminate\Http\Request;
use Laravel\Cashier\Exceptions\IncompletePayment;

class CheckoutController extends Controller
{
    private $planService;
    private $subscriptionService;

    public function __construct(PlanService $planService, SubscriptionService $subscriptionService)
    {
        $this->planService = $planService;
        $this->subscriptionService = $subscriptionService;
    }

    public function __invoke(Request $request, string $plan)
    {
        $user = $request->user();
        $subscription = $this->subscriptionService->getUserSubscription($user);

        if ($subscription && $subscription->active()) {
            return redirect()->route('subscription.index')->withErrors(['subscription' => 'You already have an active subscription.']);
        }

        $priceId = $this->planService->getPriceId($plan, $request->input('billing_period'));

        if (!$priceId) {
            abort(400, 'Invalid price ID');
        }

        $subscriptionName = $plan === 'premium' ? 'premium_subscription' : 'premium_plus_subscription';
        $billingPeriod = $request->input('billing_period') === 'yearly' ? 'yearly' : 'monthly';

        $subscriptionName = "{$subscriptionName}_{$billingPeriod}";

        return $user->newSubscription($subscriptionName, $priceId)
            ->checkout([
                'success_url' => route('checkout.success', ['plan' => $plan]),
                'cancel_url' => route('checkout.cancel'),
            ]);
    }

    public function success(Request $request)
    {
        $plan = $request->query('plan');
        $planName = ($plan === 'premium') ? 'Premium' : (($plan === 'premium-plus') ? 'Premium+' : 'Unknown');

        return redirect()->route('subscription.index')->with('success', "Your {$planName} subscription has been successfully activated!");
    }

    public function cancel()
    {
        return redirect()->route('pricing.index')->with('info', 'You have canceled the payment process. If you change your mind, feel free to complete your subscription at any time.');
    }
}