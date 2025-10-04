<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Models\User;

class LoginController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    /**
     * Show the application's login form.
     *
     * @return \Illuminate\View\View
     */
    public function showLoginForm()
    {
        return view('auth.login');
    }

    /**
     * Handle a login request to the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function login(Request $request)
    {
        \Log::info('Login attempt started', [
            'email' => $request->email,
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent()
        ]);

        $credentials = $request->validate([
            'email' => ['required'],
            'password' => ['required'],
        ]);

        \Log::info('Validation passed', ['email' => $request->email]);

        // Check if user exists using new schema
        $user = User::where('user_email', $request->email)->first();
        if (!$user) {
            \Log::warning('User not found', ['email' => $request->email]);
            return back()->withErrors([
                'email' => 'No user found with this email address.',
            ])->onlyInput('email');
        }

        \Log::info('User found', [
            'user_email' => $user->user_email,
            'user_role' => $user->user_role
        ]);

        // Verify password manually since we use custom column names
        if (\Hash::check($request->password, $user->user_password)) {
            \Log::info('Password verified successfully');
            
            // Manually log the user in
            Auth::login($user);
            $request->session()->regenerate();

            \Log::info('User logged in and session regenerated', [
                'auth_check' => Auth::check(),
                'user_id' => Auth::id()
            ]);

            // Redirect based on role-specific dashboard route
            \Log::info('Redirecting to role dashboard', [
                'route' => $user->getDashboardRoute()
            ]);
            return redirect()->route($user->getDashboardRoute());
        }

        \Log::warning('Password verification failed', [
            'email' => $request->email,
            'password_provided' => !empty($request->password)
        ]);

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    /**
     * Log the user out of the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}