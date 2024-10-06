<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        // Find the user by email
        $user = User::where('email', $request->input('email'))->first();
    
        // Check if the user exists and has no password (OAuth users typically have no password)
        if ($user && !$user->password) {
            return redirect()->route('login')->withErrors([
                'email' => 'These credentials do not match our records.',
            ]);
        }
    
        // Authenticate the user with the provided credentials (email and password)
        $request->authenticate();
    
        // Regenerate the session to prevent session fixation
        $request->session()->regenerate();
    
        // Redirect to the intended location with a success message
        return redirect()->intended(route('boards.index', absolute: false))
            ->with('success', 'Successfully logged in!');    
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
