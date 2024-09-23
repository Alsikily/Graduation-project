<?php

namespace App\Http\Requests;

// Classes
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

// Rules
use App\Rules\Required;
use App\Rules\CheckUserInConversationRule;

class SendMessageRequest extends FormRequest
{

    public function authorize(): bool {
        return true;
    }

    public function rules(): array {
        return [
            'content' => new Required,
            'record' => 'sometimes|nullable|file',
            'file_.*' => 'sometimes|nullable|file',
            'chat_id' => new CheckUserInConversationRule
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
