<?php

namespace App\Http\Controllers;

use Log;
use Exception;
use App\Models\User;
use App\Mail\WelcomeMail;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;

class AuthController extends Controller
{
    //
 public function register(Request $request)
{
    $fields = $request->validate([
        'name' => 'required|max:255',
        'email' => 'required|email|unique:users',
        'password' => 'required|confirmed|min:8',
        'role' => 'required'
    ]);

    $user = User::create([
        'name' => $fields['name'],
        'email' => $fields['email'],
        'password' => Hash::make($fields['password']),
        'role' => $fields['role'],
    ]);

    $token = $user->createToken('auth-token')->plainTextToken;

    try {
        Mail::to($user->email)->send(new WelcomeMail($user));
    } catch (Exception $e) {
        Log::error('Welcome email failed: ' . $e->getMessage());
    }

    return response()->json([
        'user' => $user,
        'token' => $token,
        'message' => "User Created Successfully"
    ]);
}

public function login(Request $request)
{
    $request->validate([
        'email' => 'required|email',
        'password' => 'required'
    ]);

    $user = User::where('email', $request->email)->first();

    if ($user && Hash::check($request->password, $user->password)) {
        $token = $user->createToken('auth-token')->plainTextToken;

        return response()->json([
            'account_type' => 'user',
            'role' => $user->role,
            'user' => $user,
            'token' => $token,
        ]);
    }

    return response()->json(['message' => 'Invalid credentials'], 401);
}

    public function logout(Request $request){
        $request->user()->tokens()->delete();
        return[
            'message'=>'You are logged out'
        ];
    }
    
     public function updateProfile(Request $request)
    {
        $user = $request->user();

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,

        ]);

        $user->update($data);
        return response()->json($user);
    }
    public function deleteAccount(Request $request)
{
     $request->validate([
        'password' => 'required|string',
    ]);

    $user = $request->user();

    if (!Hash::check($request->password, $user->password)) {
        return response()->json(['message' => 'Incorrect password'], 403);
    }

    $user->tokens()->delete();
    $user->delete();

    return response()->json(['message' => 'Account deleted successfully']);
}
public function sendResetLinkEmail(Request $request)
{
    $request->validate([
        'email' => 'required|email',
    ]);

    $status = Password::sendResetLink(
        $request->only('email')
    );

    return $status === Password::RESET_LINK_SENT
        ? response()->json(['message' => 'Password reset link sent!'])
        : response()->json(['message' => 'Failed to send reset link.'], 400);
}
 public function reset(Request $request)
{
    $request->validate([
        'token'    => 'required',
        'email'    => 'required|email',
        'password' => 'required|min:8|confirmed',
    ]);

    $status = Password::reset(
        $request->only('email', 'password', 'password_confirmation', 'token'),
        function ($user, $password) {
            $user->forceFill([
                'password'       => Hash::make($password),
                'remember_token' => Str::random(60),
            ])->save();
        }
    );

    return $status === Password::PASSWORD_RESET
        ? response()->json(['message' => 'Password reset successful.'])
        : response()->json(['message' => __($status)], 400);
}
}
