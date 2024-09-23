<?php

namespace App\Http\Requests;

use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Database\Query\Builder;

class AddFriendRequest extends FormRequest {

    public function authorize(): bool {
        return true;
    }

    public function rules(): array {
        return [
            'friend_id' => [
                'required'
                // Rule::unique('user_requests', 'sender_id') -> where(function (Builder $query) {
                //     return $query -> where('user_id', Auth::user() -> id) -> where('status', '!=', 'pending');
                // })
            ]
        ];
    }

    protected function failedValidation(Validator $validator) {

        throw new HttpResponseException(response() -> json([
            'status' => 'error',
            'messages' => $validator -> errors()
        ]));

    }

}
