<?php

namespace App\Http\Resources\Chat;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MessageResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'status' => 'success',
            'data' => [
                'id' => $this -> id,
                'content' => $this -> content,
                'record' => $this -> record,
                'files' => $this -> files,
                'read_users' => $this -> read_users,
                'read_at' => $this -> read_at,
                'sent_at' => $this -> sent_at,
                'conversation_id' => $this -> conversation_id
            ]
        ];
    }
}
