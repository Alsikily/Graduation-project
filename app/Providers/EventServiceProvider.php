<?php

namespace App\Providers;

use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;

// Events
use App\Events\AddFriendEvent;
use App\Events\SendMessageEvent;

// Listeners
use App\Listeners\AddFriendListener;
use App\Listeners\SendMessageListener;

class EventServiceProvider extends ServiceProvider {

    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
        AddFriendEvent::class => [
            AddFriendListener::class
        ],
        AddFriendListener::class => [
            SendMessageListener::class
        ]
    ];

    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        //
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     */
    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}
