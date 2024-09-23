<?php

namespace App\Http\Controllers;

// Requests
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;

// Interfaces
use App\Repository\Auth\AuthRepoInterface;

// Models
use App\Models\User;

class AuthController extends Controller {

    private $AuthRepo;

    public function __construct(AuthRepoInterface $AuthRepo) {

        $this -> AuthRepo = $AuthRepo;

    }

    public function login(LoginRequest $request) {

        return $this -> AuthRepo -> login($request);

    }

    public function register(RegisterRequest $request) {

        return $this -> AuthRepo -> register($request);

    }

    public function logout() {

        return $this -> AuthRepo -> logout();

    }

    // public function refresh() {

    //     return $this -> AuthRepo -> refresh();

    // }

}
