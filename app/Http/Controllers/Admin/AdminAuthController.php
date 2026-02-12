<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Hash;

class AdminAuthController extends Controller
{
    // login method
       public function adminLogin(Request $request)
    {
        $data = $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        $user = User::where('email', $data['email'])->where('role','admin')->first();

        if (! $user || ! Hash::check($data['password'], $user->password)) {
            dd($request->all());
            return response()->json([
                'message' => 'Invalid credentials',
            ], 401);
        }

        $token = $user->createToken('admin_token')->plainTextToken;

        return response()->json([
            'access_token' => $token,
            'role' => $user->role,
        ], 200);
    }

    //seller side login 
     public function sellerLogin(Request $request)
    {
        $data = $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        $user = User::where('email', $data['email'])->where('role','seller')->first();

        if (! $user || ! Hash::check($data['password'], $user->password)) {
            return response()->json([
                'message' => 'Invalid credentials',
            ], 401);
        }

        $token = $user->createToken('seller_token')->plainTextToken;

        return response()->json([
            'access_token' => $token,
            'role' => $user->role,
        ], 200);
    }
}
