<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        $user = User::where('email', $request->email)->first();
        if ($user) {
            $passwordMatch = Hash::check($request->password, $user->password);
        } else {
            \Log::warning('User not found with email: ' . $request->email);
        }

        if ($passwordMatch && Auth::attempt($credentials)) {
            return redirect()->intended('/admin'); // Change this to intended route after login
        }

        return redirect()->back()->with('error', 'These credentials do not match our records.');
    }
}
