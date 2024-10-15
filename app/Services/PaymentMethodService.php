<?php

namespace App\Services;

use App\Models\User;

class PaymentMethodService
{
    public function addPaymentMethod(User $user, string $paymentMethodId, bool $setAsDefault = false)
    {
        $user->addPaymentMethod($paymentMethodId);

        if ($setAsDefault) {
            $this->setDefaultPaymentMethod($user, $paymentMethodId);
        }
    }

    public function setDefaultPaymentMethod(User $user, string $paymentMethodId)
    {
        $user->updateDefaultPaymentMethod($paymentMethodId);

        foreach ($user->subscriptions as $subscription) {
            if ($subscription->active()) {
                $subscription->updateStripeSubscription([
                    'default_payment_method' => $paymentMethodId,
                ]);
            }
        }
    }
}