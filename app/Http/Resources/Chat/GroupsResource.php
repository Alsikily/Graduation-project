<?php

namespace App\Http\Resources\Chat;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GroupsResource extends JsonResource {

    public function toArray(Request $request): array {
        return [
            'status' => 'success',
            'data' => [
                'title' => $this -> title,
                'conversation_id' => $this -> conversation_id
            ]
        ];
    }

}
