<?php

namespace App\Http\Requests;

use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest {

    public function authorize(): bool {
        return true;
    }

    public function rules(): array {

        return [
            "name" => "required|string|min:5|max:50",
            "email" => "required|email|unique:users,email|max:255",
            "password" => "required|string|min:6|max:255",
        ];

    }

    protected function failedValidation(Validator $validator) {

        throw new HttpResponseException(response() -> json([
            'status' => 'error',
            'messages' => $validator -> errors()
        ]));

    }

}
