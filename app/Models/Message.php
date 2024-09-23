<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

// Models
use App\Models\User;

class Message extends Model {

    use HasFactory;
    protected $guarded = [];

    public function user() {
        return $this -> belongsTo(User::class, 'sender_id', 'id');
    }

    public function getCreatedAtAttribute($value) {
        return Carbon::parse($value) -> diffForHumans();
    }

    public function getRecordAttribute($value) {
        return asset('storage/attachements/' . $value);
    }

    public function getFilesAttribute($value) {

        if ($value != null) {

            $files = json_decode($value);
            $newFiles = [];
            foreach($files as $file) {
                $newFiles[] = asset('storage/attachements/' . $file);
            }
    
            return $newFiles;

        }

        return $value;

    }

}
