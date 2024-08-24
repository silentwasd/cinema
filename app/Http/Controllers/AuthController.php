<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $data = $request->validate([
            'email'    => 'required|string|email|min:3',
            'password' => 'required|string|min:8|max:64'
        ]);

        $user = User::where('email', $data['email'])->firstOrFail();

        if (!Hash::check($data['password'], $user->password))
            abort(404);

        return response()->json([
            'token' => $user->createToken('auth')->plainTextToken
        ]);
    }
}
