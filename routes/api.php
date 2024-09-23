<?php

use Illuminate\Support\Facades\Route;

// Controllers
use App\Http\Controllers\{
    AuthController,
    FriendsController,
    ChatsController
};

// Authentication
Route::post('login', [AuthController::class, 'login']);
Route::post('register', [AuthController::class, 'register']);
Route::post('logout', [AuthController::class, 'logout']) -> middleware('auth');

Route::group([
    'middleware' => 'auth',
    'prefix' => 'friends'
], function() {

    Route::get('search/{friend_search_name?}', [FriendsController::class, 'search']); // Search About friend name
    Route::get('recents', [FriendsController::class, 'recents']); // Get all recents chats
    Route::get('groups', [FriendsController::class, 'groups']); // Get groups
    Route::post('groups', [FriendsController::class, 'createGroup']); // Add group
    Route::get('friends', [FriendsController::class, 'friends']); // Get friends
    Route::get('archived', [FriendsController::class, 'GetArchived']); // Get Archived
    Route::get('requests', [FriendsController::class, 'requests']); // Get All friends requests
    Route::get('sends', [FriendsController::class, 'sends']); // Get All sends friends requests
    Route::post('', [FriendsController::class, 'add']); // Send add friend request
    Route::post('response/{request_id}', [FriendsController::class, 'response']); // Approve or refuse add friend request
    Route::patch('{chat_id}/block', [FriendsController::class, 'BlockUser']); // Block user
    Route::post('suggestion', [FriendsController::class, 'getSuggestion']); // Get Suggestion

});

Route::group([
    'middleware' => 'auth',
    'prefix' => 'chats'
], function() {

    Route::get('{chat_id}', [ChatsController::class, 'getChat']); // Get Conversation
    Route::post('{chat_id}/send', [ChatsController::class, 'sendMessage']); // Send Message in conversation
    Route::delete('{chat_id}/delete', [ChatsController::class, 'deleteChat']); // Delete Conversation
    Route::patch('{chat_id}', [ChatsController::class, 'archiveChat']); // Archive Conversation
    Route::post('addMember', [ChatsController::class, 'addMember']); // Add member to group
    Route::delete('{chat_id}/{memberID}/removeMember', [ChatsController::class, 'removeMember']); // Delete Conversation

});