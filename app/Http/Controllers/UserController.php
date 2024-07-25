<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use App\Mail\ConfirmEmail;
use App\Mail\ForgotPassword;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum', ['except' => ['authUser', 'preRegister', 'registerUser', 'forgotPassword']]);
    }

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
            return response()->json(['error' => $validator->errors()], 400);
        }

        $user = User::create([
            'first_name' => $request->firstName,
            'last_name' => $request->lastName,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'telephone' => $request->telephone,
            'country' => $request->country,
            'address' => $request->address,
            'address_cont' => $request->addressCont,
            'state' => $request->state,
            'city' => $request->city,
            'postal_code' => $request->postalCode,
        ]);

        if ($user) {
            $token = $user->createToken('Personal Access Token')->plainTextToken;
            return response()->json([
                'user' => $user,
                'token' => $token,
            ], 201);
        } else {
            return response()->json(['error' => 'Invalid user data'], 400);
        }
    }

    // Get user profile
    public function getUserProfile()
    {
        $user = Auth::user();

        if ($user) {
            return response()->json($user, 200);
        } else {
            return response()->json(['error' => 'User not found'], 404);
        }
    }

    // Update user profile
    public function updateUserProfile(Request $request)
    {
        $user = Auth::user();

        if ($user) {
            $user->first_name = $request->firstName ?? $user->first_name;
            $user->last_name = $request->lastName ?? $user->last_name;
            if ($request->password) {
                $user->password = Hash::make($request->password);
            }
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

    // Get all users
    public function getUsers()
    {
        $users = User::all();
        return response()->json($users, 200);
    }

    // Delete user
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

    // Get user by ID
    public function getUserById($id)
    {
        $user = User::find($id);

        if ($user) {
            return response()->json($user, 200);
        } else {
            return response()->json(['error' => 'User not found'], 404);
        }
    }

    // Update user
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

    // Forgot password
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
            $resetCode = bin2hex(random_bytes(16));
            $user->reset_code = $resetCode;
            $user->save();

            Mail::to($request->email)->send(new ForgotPassword($resetCode));

            return response()->json(['ok' => true], 200);
        } else {
            return response()->json(['error' => 'User not found'], 404);
        }
    }
}

