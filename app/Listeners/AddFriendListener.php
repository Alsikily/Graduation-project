<?php

namespace App\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

// Events
use App\Events\AddFriendEvent;

class AddFriendListener {

    public function __construct() {

    }

    public function handle(AddFriendEvent $event): void {

    }

}
