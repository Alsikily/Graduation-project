<?php

namespace App\Providers\Custom;

use Illuminate\Support\ServiceProvider;

// Repo
use App\Repository\Auth\AuthRepoInterface;
use App\Repository\Auth\AuthRepo;

class AuthProvider extends ServiceProvider {

    public function register(): void {
        $this -> app -> bind(AuthRepoInterface::class, AuthRepo::class);
    }

    public function boot(): void {

    }

}
