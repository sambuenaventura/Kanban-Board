<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

class SocialAuthController extends Controller
{
    public function handleProviderCallback()
    {
        $githubUser =  Socialite::driver('github')->user();

        $user = User::updateOrCreate([
            'github_id' => $githubUser->id
        ], [
            'name' => $githubUser->name,
            'email' => $githubUser->email,
            'github_token' => $githubUser->token,
            'github_refresh_token' => $githubUser->refresh_token,
        ]);
    
        Auth::Login($user);
        
        return redirect('/dashboard');
    }
}
