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
        try {
            $githubUser = Socialite::driver('github')->user();

            // Ensure that GitHub returns an email
            if (!$githubUser->email) {
                return redirect('/login')->with('error', 'Email not provided by GitHub. Please provide your email.');
            }

            // Check for email conflicts
            $existingUser = User::where('email', $githubUser->email)->first();
            if ($existingUser && $existingUser->github_id !== $githubUser->id) {
                return redirect('/login')->with('error', 'Email already in use by another account.');
            }

            // Update or create the user with GitHub data
            $user = User::updateOrCreate([
                'github_id' => $githubUser->id,
            ], [
                'name' => $githubUser->name,
                'email' => $githubUser->email,
                'github_token' => $githubUser->token,
                'github_refresh_token' => $githubUser->refresh_token,
            ]);

            Auth::login($user);

            return redirect('/boards')->with('success', 'Successfully logged in!');
        } 
        catch (\Exception $e) {
            // Log the error for debugging and redirect with a user-friendly message
            return redirect('/login')->with('error', 'There was an issue logging in with GitHub. Please try again.');
        }
    }
    
}
