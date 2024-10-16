<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Cashier\Billable;
use Laravel\Cashier\Subscription;

class User extends Authenticatable
{
    use HasFactory, Notifiable, Billable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'github_id',
        'github_token',
        'github_refresh_token',
        'has_lifetime_access',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function boards()
    {
        return $this->hasMany(Board::class);
    }

    public function boardUsers()
    {
        return $this->hasMany(BoardUser::class);
    }

    public function invitationCount()
    {
        return BoardInvitation::where('user_id', $this->id)
            ->where('status', 'pending')
            ->count();
    }
    
    public function getActiveSubscription()
    {
        return $this->subscriptions()->where('stripe_status', 'active')->first();
    }

    public function subscriptions()
    {
        return $this->hasMany(Subscription::class);
    }
    
    public function hasPremiumAccess()
    {
        // Check if the user has an active premium subscription (Monthly or Yearly)
        $activeSubscription = $this->getActiveSubscription();
        return $activeSubscription && in_array($activeSubscription->stripe_price, [
            'price_1Q9SGkAtSEuPnXfeOlmPDRa1', // Monthly
            'price_1Q9SKtAtSEuPnXfe8UjEWMKy'  // Yearly
        ]);
    }
    
    public function hasPremiumPlusAccess()
    {
        // Check if the user has an active premium plus subscription (Monthly or Yearly)
        $activeSubscription = $this->getActiveSubscription();
        return $activeSubscription && in_array($activeSubscription->stripe_price, [
            'price_1Q9SMFAtSEuPnXfeQmMdMgQg', // Monthly
            'price_1Q9SMfAtSEuPnXfeN6yuqYqm'   // Yearly
        ]);
    }
    
    public function hasMonthlyAccess()
    {
        // Check if the user has an active monthly subscription (Premium or Premium+)
        $activeSubscription = $this->getActiveSubscription();
    
        return $activeSubscription && in_array($activeSubscription->stripe_price, [
            'price_1Q9SGkAtSEuPnXfeOlmPDRa1', // Premium Monthly
            'price_1Q9SMFAtSEuPnXfeQmMdMgQg', // Premium+ Monthly
        ]);
    }
    
    public function hasYearlyAccess()
    {
        // Check if the user has an active yearly subscription (Premium or Premium+)
        $activeSubscription = $this->getActiveSubscription();
    
        return $activeSubscription && in_array($activeSubscription->stripe_price, [
            'price_1Q9SKtAtSEuPnXfe8UjEWMKy', // Premium Yearly
            'price_1Q9SMfAtSEuPnXfeN6yuqYqm',  // Premium+ Yearly
        ]);
    }
    
    public function hasLifetimeAccess()
    {
        return $this->has_lifetime_access;
    }

    public function grantLifetimeAccess()
    {
        $this->has_lifetime_access = true;
        $this->save();
    }

}
