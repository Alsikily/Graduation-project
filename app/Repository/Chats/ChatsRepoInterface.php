<?php

namespace App\Repository\Chats;

interface ChatsRepoInterface {

    public function getChat($chat_id);
    public function sendMessage($request, $chat_id);
    public function deleteChat($chat_id);
    public function archiveChat($request, $chat_id);
    public function addMember($request);
    public function removeMember($chat_id, $memberID);

}