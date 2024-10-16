<?php

namespace App\Services;

use App\Models\User;
use Laravel\Cashier\Cashier;

class SubscriptionService
{
    private $planService;

    public function __construct(PlanService $planService)
    {
        $this->planService = $planService;
    }

    public function getUserSubscription(User $user)
    {
        return $user->subscriptions()
            ->where('stripe_status', 'active')
            ->first();
    }

    public function getSubscriptionDetails(User $user)
    {
        $subscription = $this->getUserSubscription($user);

        if (!$subscription) {
            return [
                'subscription' => null,
                'planName' => null,
                'billingFrequency' => 'N/A',
            ];
        }

        $subscription->refresh();
        $subscriptionData = $subscription->asStripeSubscription();

        if (empty($subscriptionData->items->data)) {
            return [
                'subscription' => $subscription,
                'planName' => 'No Plan',
                'billingFrequency' => 'N/A',
            ];
        }

        $plan = $subscriptionData->items->data[0]->plan;
        $planName = $this->getPlanNameFromPriceId($plan->id);
        $billingFrequency = $plan->interval === 'month' ? 'Monthly' : 'Annually';

        return [
            'subscription' => $subscription,
            'planName' => $planName,
            'billingFrequency' => $billingFrequency,
        ];
    }

    public function getPricingPlans($excludeBasic = true)
    {
        $plans = $this->planService->getPricingPlans($excludeBasic);

        foreach ($plans as &$plan) {
            if (isset($plan['slug'])) {
                $plan['route'] = route('subscription.change-plan.billing', ['plan' => $plan['slug']]);
            }
        }

        return $plans;
    }

    public function changePlan(User $user, string $plan, string $billingPeriod)
    {
        $priceId = $this->planService->getPriceId($plan, $billingPeriod);

        if (!$priceId) {
            throw new \InvalidArgumentException('Invalid plan or billing period');
        }

        $subscription = $this->getUserSubscription($user);

        if ($subscription && $subscription->stripe_price === $priceId) {
            throw new \InvalidArgumentException('You are already subscribed to this plan.');
        }

        if ($subscription) {
            $subscription->swap($priceId);
        } else {
            $subscriptionName = $plan === 'premium' ? 'premium_subscription' : 'premium_plus_subscription';
            $user->newSubscription($subscriptionName, $priceId)->create();
        }
    }

    private function getPlanNameFromPriceId($priceId)
    {
        $planNames = [
            'price_1Q9SGkAtSEuPnXfeOlmPDRa1' => 'Premium Monthly',
            'price_1Q9SKtAtSEuPnXfe8UjEWMKy' => 'Premium Annually',
            'price_1Q9SMFAtSEuPnXfeQmMdMgQg' => 'Premium+ Monthly',
            'price_1Q9SMfAtSEuPnXfeN6yuqYqm' => 'Premium+ Annually',
        ];

        return $planNames[$priceId] ?? 'Unknown Plan';
    }

    public function getMaxBoards($user)
    {
        // Define board limits for each subscription plan
        switch (true) {
            case $user->hasPremiumPlusAccess():
                return PHP_INT_MAX; // Unlimited for Premium
            case $user->hasPremiumAccess():
                return PHP_INT_MAX; // Unlimited for Premium+
            default:
                return 2; // Basic plan limit
        }
    }

}