<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SendMessageEvent implements ShouldBroadcast {

    use Dispatchable, InteractsWithSockets, SerializesModels;

    public string $message;
    public int $friendID;

    public function __construct($friendID, $message) {
        $this -> friendID = $friendID;
        $this -> message = $message;
    }

    public function broadcastOn(): array {
        return [
            'privateChannel.' . $this -> friendID
        ];
    }

    public function broadcastAs(): string {
        return 'send-message';
    }

}
