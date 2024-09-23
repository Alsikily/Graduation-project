<?php

namespace App\Repository\Friends;

interface FriendsRepoInterface {

    public function searchAboutFriend($request);
    public function addFriend($request);
    public function recents();
    public function groups();
    public function createGroup($request);
    public function friends();
    public function GetArchived();
    public function requests();
    public function sends();
    public function response($request, $request_id);
    public function BlockUser($request, $chat_id);
    public function getSuggestion($request);

}