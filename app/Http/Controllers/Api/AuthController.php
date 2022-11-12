<?php

namespace App\Http\Controllers\Api;

use App\Enums\Role;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $request->validate([
            'email' => 'required|email|unique:users',
            'password' => 'required|confirmed',
        ]);

        $result = User::create([
            'email' => $request->get('email'),
            'password' => bcrypt($request->get('password')),
            'role' => Role::PHOTOGRAPHER->name,
        ]);

        return $result;
    }


    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if(Auth::attempt($credentials)) {
            /** @var User $user */
            $user = Auth::user();
            $token = md5(time()) . '.' . md5($request->get('email'));

            $user->forceFill(['api_token' => $token])->save();

            return response()->json([
                'token' => $token
            ]);
        }

        return response()->json([
            'message' => 'The provided credentials do not match our records'
        ]);
    }


    public function logout(Request $request)
    {
        $request->user()->forceFill([
            'api_token' => null,
        ])->save();

        return response()->json(['message' => 'success']);
    }
}
