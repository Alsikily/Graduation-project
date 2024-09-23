<?php

namespace App\Providers\Custom;

use Illuminate\Support\ServiceProvider;

// Repo
use App\Repository\Chats\ChatsRepoInterface;
use App\Repository\Chats\ChatsRepo;

class ChatsProvider extends ServiceProvider {

    public function register(): void {
        $this -> app -> bind(ChatsRepoInterface::class, ChatsRepo::class);
    }

    public function boot(): void {

    }

}
