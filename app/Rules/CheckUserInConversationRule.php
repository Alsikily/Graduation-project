<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Auth;

// Models
use App\Models\UserConversation;

class CheckUserInConversationRule implements ValidationRule {

    private function CheckUserInConversation($conversation_id) {

        $exists = UserConversation::where('conversation_id', $conversation_id)
                                    -> where('user_id', Auth::user() -> id)
                                    -> first();

        return $exists;

    }

    public function validate(string $attribute, mixed $value, Closure $fail): void {

        if (!$this -> CheckUserInConversation($value)) {
            $fail("User not exists in this conversation");
        }

    }

}
