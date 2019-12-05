<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Traits\CustomTraits;
use App\User;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    use CustomTraits;

    /**
     * Register new users to the app
     * 
     * @param \App\Http\Requests\RegisterRequest $request
     *
     * @return Illuminate\Http\JsonResponse
     */
    public function register(RegisterRequest $request)
    {
        User::create($request->all());

        return $this->responseCodes('success', 'Registered successfully, please login.');
    }

    /**
     * Login a user
     * 
     * @param \App\Http\Requests\LoginRequest $request
     *
     * @return Illuminate\Http\JsonResponse
     */
    public function login(LoginRequest $request)
    {
        if ($token = Auth::guard('users')->attempt($request->only(['email', 'password']))) {

            $user = Auth::guard('users')->user();

            return response()->json([
                'status' => 'success',
                'user' => $user,
                'token' => $token
            ]);
        } else {
            return $this->responseCodes('error', 'Email or password is invalid.', 401);
        }
    }

    /**
     * Log out a user
     * 
     * @return Illuminate\Http\JsonResponse
     */
    public function logOut()
    {
        Auth::guard('users')->logout(true);

        return $this->responseCodes('success', 'You just logged out');
    }
}
