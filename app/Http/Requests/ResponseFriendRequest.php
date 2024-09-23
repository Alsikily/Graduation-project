<?php

namespace App\Http\Requests;

use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\Auth;

class ResponseFriendRequest extends FormRequest {

    public function authorize(): bool {
        return true;
    }

    public function rules(): array {
        return [
            'status' => 'required:in:approved,refused',
            'request_id' => [
                'required',
                Rule::exists('user_requests', 'id') -> where(function (Builder $query) {
                    return $query -> where('user_id', Auth::user() -> id) -> where('status', '=', 'pending');
                })
            ]
        ];
    }

    public function validationData() {
        return array_merge($this->route()->parameters(), $this->all());
    }

    protected function failedValidation(Validator $validator) {

        throw new HttpResponseException(response() -> json([
            'status' => 'error',
            'messages' => $validator -> errors()
        ]));

    }

}
