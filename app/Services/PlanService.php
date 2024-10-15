<?php

namespace App\Services;

use Laravel\Cashier\Cashier;

class PlanService
{
    private const PRICE_IDS = [
        'premium' => [
            'monthly' => 'price_1Q9SGkAtSEuPnXfeOlmPDRa1',
            'yearly' => 'price_1Q9SKtAtSEuPnXfe8UjEWMKy',
        ],
        'premium-plus' => [
            'monthly' => 'price_1Q9SMFAtSEuPnXfeQmMdMgQg',
            'yearly' => 'price_1Q9SMfAtSEuPnXfeN6yuqYqm',
        ],
    ];

    public function getPricingPlans($excludeBasic = false)
    {
        $premiumMonthlyPrice = $this->getPrice(self::PRICE_IDS['premium']['monthly']);
        $premiumYearlyPrice = $this->getPrice(self::PRICE_IDS['premium']['yearly']);
        $premiumPlusMonthlyPrice = $this->getPrice(self::PRICE_IDS['premium-plus']['monthly']);
        $premiumPlusYearlyPrice = $this->getPrice(self::PRICE_IDS['premium-plus']['yearly']);
    
        $plans = [
            [
                'name' => 'Basic',
                'description' => 'A simple way to organize your tasks. Ideal for personal use.',
                'monthly_price' => '0',
                'yearly_price' => '0',
                'features' => [
                    'Create up to 2 boards',
                    'Create up to 15 tasks per board',
                    'No collaborators',
                ],
                'route' => '',
            ],
            [
                'slug' => 'premium',
                'name' => 'Premium',
                'description' => 'Best for managing multiple projects with small teams.',
                'monthly_price' => $premiumMonthlyPrice->unit_amount / 100,
                'yearly_price' => $premiumYearlyPrice->unit_amount / 100,
                'features' => [
                    'Unlimited boards',
                    'Unlimited tasks',
                    'Invite up to 2 collaborators',
                    'File attachments (up to 5MB per file)',
                ],
                'route' => route('pricing.billing', ['plan' => 'premium']),
            ],
            [
                'slug' => 'premium-plus',
                'name' => 'Premium+',
                'description' => 'Designed for larger teams with added flexibility and support.',
                'monthly_price' => $premiumPlusMonthlyPrice->unit_amount / 100,
                'yearly_price' => $premiumPlusYearlyPrice->unit_amount / 100,
                'features' => [
                    'All Premium features',
                    'Priority support',
                    'Invite up to 5 collaborators',
                    'Larger file attachments (up to 12MB per file)',
                ],
                'route' => route('pricing.billing', ['plan' => 'premium-plus']),
            ],
        ];
    
        // If excludeBasic is true, filter out the 'Basic' plan
        if ($excludeBasic) {
            return array_filter($plans, function($plan) {
                return $plan['name'] !== 'Basic';
            });
        }
    
        return $plans;
    }
    
    public function getPriceId(string $plan, string $billingPeriod): ?string
    {
        return self::PRICE_IDS[$plan][$billingPeriod] ?? null;
    }

    private function getPrice($priceId)
    {
        return Cashier::stripe()->prices->retrieve($priceId);
    }

    public function getPriceAmount($plan, $billingPeriod)
    {
        $priceId = $this->getPriceId($plan, $billingPeriod);
        $price = Cashier::stripe()->prices->retrieve($priceId);
        return $price->unit_amount / 100;
    }
}