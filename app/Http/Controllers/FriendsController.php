<?php

namespace App\Http\Controllers;

// Requests
use App\Http\Requests\AddFriendRequest;
use App\Http\Requests\ResponseFriendRequest;
use App\Http\Requests\groupRequest;

// Interfaces
use App\Repository\Friends\FriendsRepoInterface;
use Illuminate\Http\Request;

class FriendsController extends Controller {

    private $FriendsRepo;

    public function __construct(FriendsRepoInterface $FriendsRepo) {
        $this -> FriendsRepo = $FriendsRepo;
    }

    public function search(Request $request) {
        return $this -> FriendsRepo -> searchAboutFriend($request);
    }

    public function add(AddFriendRequest $request) {
        return $this -> FriendsRepo -> addFriend($request);
    }
    
    public function recents() {
        return $this -> FriendsRepo -> recents();
    }

    public function groups() {
        return $this -> FriendsRepo -> groups();
    }

    public function createGroup(groupRequest $request) {
        return $this -> FriendsRepo -> createGroup($request);
    }

    public function friends() {
        return $this -> FriendsRepo -> friends();
    }

    public function GetArchived() {
        return $this -> FriendsRepo -> GetArchived();
    }

    public function requests() {
        return $this -> FriendsRepo -> requests();
    }

    public function sends() {
        return $this -> FriendsRepo -> sends();
    }

    public function response(ResponseFriendRequest $request, $request_id) {
        return $this -> FriendsRepo -> response($request, $request_id);
    }

    public function BlockUser(Request $request, $chat_id) {
        return $this -> FriendsRepo -> BlockUser($request, $chat_id);
    }

    public function getSuggestion(Request $request) {
        return $this -> FriendsRepo -> getSuggestion($request);
    }

}
