<?php

namespace App\Providers;

use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\ServiceProvider;

class BroadcastServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     */
    public function boot(): void {

        // Broadcast::routes(['prefix' => 'api', "middleware" => ['auth']]);
        Broadcast::routes();

        require base_path('routes/channels.php');

    }
}
