<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use App\Notifications\ConfirmEmail;
use App\Notifications\WelcomeEmail;
use App\Notifications\ForgotPassword;
use Illuminate\Support\Str;

class UserController extends Controller
{
    // Auth User & Get Token
    public function authUser(Request $request)
    {
        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials)) {
            $user = Auth::user();
            $token = $user->createToken('Personal Access Token')->plainTextToken;

            return response()->json([
                'user' => $user,
                'token' => $token,
            ], 200);
        } else {
            return response()->json(['error' => 'Invalid email or password'], 401);
        }
    }

    // Create JWT with Email & Password; send confirmation email
    public function preRegister(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        $userExists = User::where('email', $request->email)->exists();
        if ($userExists) {
            return response()->json(['error' => 'Email is taken'], 400);
        }

        $token = bin2hex(random_bytes(32));
        $data = [
            'email' => $request->email,
            'password' => $request->password,
            'token' => $token,
        ];

        Mail::to($request->email)->send(new ConfirmEmail($data));

        return response()->json(['ok' => true], 200);
    }

// Register New User
    public function registerUser(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'firstName' => 'required|string',
            'lastName' => 'required|string',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6',
            'telephone' => 'required|string',
            'country' => 'required|string',
            'address' => 'required|string',
            'addressCont' => 'string|nullable',
            'state' => 'required|string',
            'city' => 'required|string',
            'postalCode' => 'required|string',
        ]);

        if ($validator->fails()) {
            if ($request->expectsJson()) {
                return response()->json(['error' => $validator->errors()], 400);
            }
            return back()->withErrors($validator)->withInput();
        }

        $token = Str::random(60);

        $user = new User();
        $user->first_name = $request->firstName;
        $user->last_name = $request->lastName;
        $user->email = $request->email;
        $user->password = $request->password;
        $user->telephone = $request->telephone;
        $user->country = $request->country;
        $user->address = $request->address;
        $user->address_cont = $request->addressCont;
        $user->state = $request->state;
        $user->city = $request->city;
        $user->postal_code = $request->postalCode;
        $user->email_verified = false;
        $user->email_verification_token = $token;

        $user->save();

        if ($user) {
            $user->notify(new ConfirmEmail($token));

            if ($request->expectsJson()) {
                return response()->json(['success' => 'Registration successful! Please check your email to verify your account.'], 201);
            }
            return redirect()->route('register-form')->with('success', 'Registration successful! Please check your email to verify your account.');
        } else {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'Registration failed! Please try again.'], 500);
            }
            return back()->with('error', 'Registration failed! Please try again.');
        }
    }


// Verify Email and Send Welcome Email
    public function verifyEmail($token)
    {
        $user = User::where('email_verification_token', $token)->first();

        if ($user) {
            $user->email_verified = true;
            $user->email_verification_token = null;
            $user->save();

            $user->notify(new WelcomeEmail());

            // Make sure this points to your App Runner URL
            return redirect(env('APP_CLIENT_URL', 'https://xx82fv3rgu.us-east-1.awsapprunner.com') . '/verified')
                ->with('success', 'Email verified successfully. Welcome!');
        } else {
            return redirect(env('APP_CLIENT_URL', 'https://xx82fv3rgu.us-east-1.awsapprunner.com') . '/verified')
                ->with('error', 'Invalid token. Email verification failed.');
        }
    }


    // Forgot Password
    public function forgotPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        $user = User::where('email', $request->email)->first();

        if ($user) {
            $token = Str::random(60);
            $user->password_reset_token = $token;
            $user->save();

            $user->notify(new ForgotPassword($token));

            return response()->json(['message' => 'Password reset email sent.'], 200);
        } else {
            return response()->json(['error' => 'User not found.'], 404);
        }
    }

    // Reset Password
    public function resetPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|min:6|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        $user = User::where('email', $request->email)->where('password_reset_token', $request->token)->first();

        if (!$user) {
            return response()->json(['error' => 'Invalid token or email'], 400);
        }

        $user->password = $request->password;
        $user->password_reset_token = null; // Clear the reset token
        $user->save();

        return response()->json(['message' => 'Password has been reset successfully.'], 200);
    }


    // Get User Profile
    public function getUserProfile()
    {
        $user = Auth::user();

        if ($user) {
            return response()->json($user, 200);
        } else {
            return response()->json(['error' => 'User not found'], 404);
        }
    }

    // Update User Profile
    public function updateUserProfile(Request $request)
    {
        $user = Auth::user();

        if ($user) {
            $user->first_name = $request->firstName ?? $user->first_name;
            $user->last_name = $request->lastName ?? $user->last_name;
            $user->email = $request->email ?? $user->email;
            $user->telephone = $request->telephone ?? $user->telephone;
            $user->country = $request->country ?? $user->country;
            $user->address = $request->address ?? $user->address;
            $user->address_cont = $request->addressCont ?? $user->address_cont;
            $user->state = $request->state ?? $user->state;
            $user->city = $request->city ?? $user->city;
            $user->postal_code = $request->postalCode ?? $user->postal_code;

            $user->save();

            return response()->json($user, 200);
        } else {
            return response()->json(['error' => 'User not found'], 404);
        }
    }

    // Get All Users
    public function getUsers()
    {
        $users = User::all();
        return response()->json($users, 200);
    }

    // Delete User
    public function deleteUser($id)
    {
        $user = User::find($id);

        if ($user) {
            $user->delete();
            return response()->json(['message' => 'User removed'], 200);
        } else {
            return response()->json(['error' => 'User not found'], 404);
        }
    }

    // Get User By ID
    public function getUserById($id)
    {
        $user = User::find($id);

        if ($user) {
            return response()->json($user, 200);
        } else {
            return response()->json(['error' => 'User not found'], 404);
        }
    }

    // Update User
    public function updateUser(Request $request, $id)
    {
        $user = User::find($id);

        if ($user) {
            $user->first_name = $request->firstName ?? $user->first_name;
            $user->last_name = $request->lastName ?? $user->last_name;
            $user->is_admin = $request->isAdmin ?? $user->is_admin;
            $user->email = $request->email ?? $user->email;
            $user->telephone = $request->telephone ?? $user->telephone;
            $user->country = $request->country ?? $user->country;
            $user->address = $request->address ?? $user->address;
            $user->address_cont = $request->addressCont ?? $user->address_cont;
            $user->state = $request->state ?? $user->state;
            $user->city = $request->city ?? $user->city;
            $user->postal_code = $request->postalCode ?? $user->postal_code;

            $user->save();

            return response()->json($user, 200);
        } else {
            return response()->json(['error' => 'User not found'], 404);
        }
    }
}


