<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

// Requests
use App\Http\Requests\SendMessageRequest;
use App\Http\Requests\archiveChat;

// Interfaces
use App\Repository\Chats\ChatsRepoInterface;

class ChatsController extends Controller {

    private $ChatsRepo;
    public function __construct(ChatsRepoInterface $ChatsRepo) {
        $this -> ChatsRepo = $ChatsRepo;
    }

    public function getChat($chat_id) {
        return $this -> ChatsRepo -> getChat($chat_id);
    }

    public function sendMessage(SendMessageRequest $request, $chat_id) {
        return $this -> ChatsRepo -> sendMessage($request, $chat_id);
    }

    public function deleteChat($chat_id) {
        return $this -> ChatsRepo -> deleteChat($chat_id);
    }

    public function archiveChat(archiveChat $request, $chat_id) {
        return $this -> ChatsRepo -> archiveChat($request, $chat_id);
    }

    public function addMember(Request $request) {
        return $this -> ChatsRepo -> addMember($request);
    }

    public function removeMember($chat_id, $memberID) {
        return $this -> ChatsRepo -> removeMember($chat_id, $memberID);
    }

}
