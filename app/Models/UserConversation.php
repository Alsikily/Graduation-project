<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

// Models
use App\Models\User;

class UserConversation extends Model {

    use HasFactory;
    protected $guarded = [];

    public function user() {
        return $this -> belongsTo(User::class, 'user_id', 'id');
    }

}
