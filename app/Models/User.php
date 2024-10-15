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
        // Check if the user has an active Premium subscription (either monthly or yearly)
        $monthlySubscription = $this->subscription('price_1Q9SGkAtSEuPnXfeOlmPDRa1');
        $yearlySubscription = $this->subscription('price_1Q9SKtAtSEuPnXfe8UjEWMKy');
    
        return $this->hasLifetimeAccess() || 
               ($monthlySubscription && $monthlySubscription->active()) || 
               ($yearlySubscription && $yearlySubscription->active());
    }
    
    public function hasPremiumPlusAccess()
    {
        // Check if the user has an active Premium+ subscription (either monthly or yearly)
        $monthlySubscription = $this->subscription('price_1Q9SMFAtSEuPnXfeQmMdMgQg');
        $yearlySubscription = $this->subscription('price_1Q9SMfAtSEuPnXfeN6yuqYqm');
    
        return $monthlySubscription && $monthlySubscription->active() || 
               $yearlySubscription && $yearlySubscription->active();
    }
    
    public function hasMonthlyAccess()
    {
        // Check if the user has an active Premium or Premium+ monthly subscription
        $premiumMonthly = $this->subscription('price_1Q9SGkAtSEuPnXfeOlmPDRa1');
        $premiumPlusMonthly = $this->subscription('price_1Q9SMFAtSEuPnXfeQmMdMgQg');
    
        return ($premiumMonthly && $premiumMonthly->active()) || 
               ($premiumPlusMonthly && $premiumPlusMonthly->active());
    }
    
    public function hasYearlyAccess()
    {
        // Check if the user has an active Premium or Premium+ yearly subscription
        $premiumYearly = $this->subscription('price_1Q9SKtAtSEuPnXfe8UjEWMKy');
        $premiumPlusYearly = $this->subscription('price_1Q9SMfAtSEuPnXfeN6yuqYqm');
    
        return ($premiumYearly && $premiumYearly->active()) || 
               ($premiumPlusYearly && $premiumPlusYearly->active());
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
