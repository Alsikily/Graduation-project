<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AddFriendEvent implements ShouldBroadcast {

    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $friend;
    public string $message;

    public function __construct($friend, $message) {

        $this -> friend = $friend;
        $this -> message = $message;

    }

    public function broadcastOn(): array {
        return [
            new PrivateChannel('private-channel')
        ];
    }

    public function broadcastAs(): string {
        return 'my-event';
    }

}
