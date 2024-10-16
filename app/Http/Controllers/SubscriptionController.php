<?php

namespace App\Http\Controllers;

use App\Services\PaymentMethodService;
use App\Services\SubscriptionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Laravel\Cashier\Cashier;
use Laravel\Cashier\Exceptions\IncompletePayment;

class SubscriptionController extends Controller
{
    private $subscriptionService;
    private $paymentMethodService;

    public function __construct(SubscriptionService $subscriptionService, PaymentMethodService $paymentMethodService)
    {
        $this->subscriptionService = $subscriptionService;
        $this->paymentMethodService = $paymentMethodService;
    }
    public function index(Request $request)
    {
        $user = $request->user();
        $subscriptionDetails = $this->subscriptionService->getSubscriptionDetails($user);
        $intent = $user->createSetupIntent();
        $defaultPaymentMethod = $user->defaultPaymentMethod();
    
        // Format the next billing date
        $nextBillingDate = null;
        if (isset($subscriptionDetails['subscription'])) {
            $subscriptionDetails['subscription']->refresh();
            $currentPeriodEnd = $subscriptionDetails['subscription']->asStripeSubscription()->current_period_end;
    
            $nextBillingDate = $subscriptionDetails['subscription']->onGracePeriod()
                ? 'N/A'
                : (is_numeric($currentPeriodEnd) 
                    ? date('F j, Y', $currentPeriodEnd) 
                    : $currentPeriodEnd->format('F j, Y'));
        }
    
        return view('subscription.index', array_merge($subscriptionDetails, [
            'intent' => $intent,
            'defaultPaymentMethod' => $defaultPaymentMethod,
            'nextBillingDate' => $nextBillingDate,
        ]));
    }
    
    public function showChangePlan()
    {
        $plans = $this->subscriptionService->getPricingPlans();
        
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
    
        return view('subscription.change-plan', compact('plans', 'currentSubscription'));
    }
    
    public function selectBillingPeriod($plan)
    {
        $plans = $this->subscriptionService->getPricingPlans();
        $selectedPlan = collect($plans)->firstWhere('slug', $plan);

        if (!$selectedPlan) {
            abort(404);
        }

        return view('subscription.billing', [
            'plan' => $plan,
            'selectedPlan' => $selectedPlan,
        ]);
    }

    public function cancel(Request $request)
    {
        $user = $request->user();
        $subscription = $this->subscriptionService->getUserSubscription($user);

        if ($subscription && $subscription->active()) {
            $subscription->cancel();
            return redirect()->route('subscription.index')->with('status', 'Subscription canceled.');
        }

        return redirect()->route('subscription.index')->with('error', 'No active subscription to cancel.');
    }

    public function resume(Request $request)
    {
        $user = $request->user();
        $subscription = $this->subscriptionService->getUserSubscription($user);

        if ($subscription && $subscription->onGracePeriod()) {
            $subscription->resume();
            return redirect()->route('subscription.index')->with('status', 'Subscription resumed.');
        }

        return redirect()->route('subscription.index')->with('error', 'No subscription to resume.');
    }

    public function changePlan(Request $request, string $plan)
    {
        try {
            $this->subscriptionService->changePlan($request->user(), $plan, $request->input('billing_period'));
            return redirect()->route('subscription.index')->with('success', 'Plan changed successfully!');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Failed to change plan: ' . $e->getMessage()]);
        }
    }

    public function editPaymentMethod(Request $request)
    {
        $user = $request->user();
        $intent = $user->createSetupIntent();
        $defaultPaymentMethod = $user->defaultPaymentMethod();
        $paymentMethods = $user->paymentMethods();

        return view('subscription.payment-method', compact('intent', 'defaultPaymentMethod', 'paymentMethods'));
    }

    public function setDefaultPaymentMethod(Request $request, $paymentMethodId)
    {
        $this->paymentMethodService->setDefaultPaymentMethod($request->user(), $paymentMethodId);
        return redirect()->route('subscription.payment-method.edit')->with('status', 'Payment method set as default successfully.');
    }

    public function addPaymentMethod(Request $request)
    {
        $request->validate([
            'payment_method' => 'required|string',
            'set_default' => 'sometimes|boolean',
        ]);

        $this->paymentMethodService->addPaymentMethod(
            $request->user(),
            $request->input('payment_method'),
            $request->input('set_default', false)
        );

        return redirect()->route('subscription.payment-method.edit')->with('status', 'Payment method added successfully.');
    }

    public function invoices(Request $request)
    {
        $user = $request->user();
        $invoices = $user->invoices();

        return view('subscription.invoices', compact('invoices'));
    }
}