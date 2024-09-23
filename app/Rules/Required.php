<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class Required implements ValidationRule {

    public function validate(string $attribute, mixed $value, Closure $fail): void {

        if (json_decode($value) === null && request() -> file('record') === null && request() -> input('files') === null) {
            $fail("content field is required");
        }

    }

}
