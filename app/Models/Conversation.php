<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

// Models
use App\Models\Message;
use App\Models\UserConversation;

class Conversation extends Model {

    use HasFactory;
    protected $guarded = [];

    public function messages() {
        return $this -> hasMany(Message::class, 'conversation_id', 'id');
    }

    public function user() {
        return $this -> hasMany(UserConversation::class, 'conversation_id', 'id');
    }

    public function members() {
        return $this -> hasMany(UserConversation::class, 'conversation_id', 'id');
    }

}
