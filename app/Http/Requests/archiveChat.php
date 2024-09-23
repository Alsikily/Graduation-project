<?php

namespace App\Http\Requests;

use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

class archiveChat extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool {
        return true;
    }

    public function rules(): array {
        return [
            'archived' => 'required|in:0,1'
        ];
    }

    protected function failedValidation(Validator $validator) {

        throw new HttpResponseException(response() -> json([
            'status' => 'error',
            'messages' => $validator -> errors()
        ]));

    }

}
