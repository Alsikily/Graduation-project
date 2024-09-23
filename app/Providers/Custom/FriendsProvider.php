<?php

namespace App\Providers\Custom;

use Illuminate\Support\ServiceProvider;

// Repo
use App\Repository\Friends\FriendsRepoInterface;
use App\Repository\Friends\FriendsRepo;

class FriendsProvider extends ServiceProvider {

    public function register(): void {
        $this -> app -> bind(FriendsRepoInterface::class, FriendsRepo::class);
    }

    public function boot(): void {

    }

}
