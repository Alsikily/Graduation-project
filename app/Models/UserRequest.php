<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;


// Models
use App\Models\User;

class UserRequest extends Model {

    use HasFactory;

    protected $guarded = [];

    public function user() {
        return $this -> BelongsTo(User::class, 'sender_id', 'id');
    }

    public function user2() {
        return $this -> BelongsTo(User::class, 'user_id', 'id');
    }

}
