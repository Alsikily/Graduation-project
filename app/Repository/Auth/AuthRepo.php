<?php

namespace App\Repository\Auth;

// Classes
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

// Interface
use App\Repository\Auth\AuthRepoInterface;

// Traits
// use App\Traits\Auth\Login;

// Models
use App\Models\User;

class AuthRepo implements AuthRepoInterface {

    public function login($request) {

        $credentials = $request -> only("email", "password");
        $token = Auth::attempt($credentials);

        if (!$token) {

            $errors = [
                "data" => ['بيانات غير صحيحة']
            ];

            return response()->json([
                'status' => 'error',
                'errors' => $errors,
            ], 401);

        }

        return response()->json([
            'status' => 'success',
            'user' => Auth::user(),
            'message' => 'تم تسجيل الدخول بنجاح',
            'authorisation' => [
                'token' => $token,
                'type' => 'bearer',
            ]
        ]);

    }

    public function register($request) {

        $request['password'] = Hash::make($request -> password);
        $credentials = $request -> only("name", "email", "password");

        $user = User::create($credentials);
        $token = Auth::login($user);

        return response() -> json([
            'status' => 'success',
            'message' => 'تم إنشاء الحساب بنجاح',
            'user' => $user,
            'authorisation' => [
                'token' => $token,
                'type' => 'bearer',
            ]
        ]);

    }

    public function logout() {

        Auth::logout();
        return response() -> json([
            'status' => 'success',
        ], 200);

    }

    // public function refresh() {

    //     return response()->json([
    //         'status' => 'success',
    //         'user' => Auth::user(),
    //         'authorisation' => [
    //             'token' => Auth::refresh(),
    //             'type' => 'bearer',
    //         ]
    //     ]);

    // }

}