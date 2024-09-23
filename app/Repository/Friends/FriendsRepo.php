<?php

namespace App\Repository\Friends;

// Classes
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Query\JoinClause;

// Resources
use App\Http\Resources\Chat\GroupsResource;
use App\Http\Resources\Friend\suggestionResource;

// Interface
use App\Repository\Friends\FriendsRepoInterface;

// Events
use App\Events\AddFriendEvent;

// Models
use App\Models\UserRequest;
use App\Models\Conversation;
use App\Models\UserConversation;
use App\Models\UserBlocks;

class FriendsRepo implements FriendsRepoInterface {

    private $StanderPagenation = 10;

    public function getUserConversations($conversation_type) {

        $data = Conversation::from('conversations as c') -> join('user_conversations as uc', function ($join) use ($conversation_type) {
            $join -> on('uc.conversation_id', '=', 'c.id')
                  -> where('uc.user_id', '=', Auth::user() -> id);
        })
        -> join('user_conversations as uc2', function ($join) use ($conversation_type) {
            $join -> on('uc2.conversation_id', '=', 'c.id')
                  -> where('uc2.user_id', '!=', Auth::user() -> id);
        })
        -> where('c.type', '=', $conversation_type)
        -> orderByDesc('uc.created_at')
        -> paginate($this -> StanderPagenation);

        return $data;

    }

    public function searchAboutFriend($request) {

        $friend_search_name = $request -> friend_search_name;
        $AuthedUserId = Auth::user() -> id;

        $users = DB::table('users')
                    -> select('users.id', 'users.name', 'users.photo')
                    -> leftJoin('user_requests', function (JoinClause $join) use ($AuthedUserId) {
                        $join -> whereRaw('(users.id = user_requests.user_id or users.id = user_requests.sender_id)')
                                -> whereRaw('(user_requests.user_id = ? or user_requests.sender_id = ?)', [$AuthedUserId, $AuthedUserId]);
                    })
                    -> where(function ($query) use ($AuthedUserId) {
                        $query -> whereNull('user_requests.user_id')
                            -> orWhere('user_requests.user_id', '!=', $AuthedUserId);
                    })
                    -> where('users.name', 'like', '%' . $friend_search_name . '%')
                    -> where(function ($query) use ($AuthedUserId) {
                        $query -> whereNull('user_requests.sender_id')
                            ->orWhere('user_requests.sender_id', '!=', $AuthedUserId);
                    })
                    -> orderBy('users.id')
                    -> distinct()
                    -> paginate(15);

        return response() -> json([
            'status' => 'success',
            'data' => $users
        ], 200);

    }

    public function addFriend($request) {

        $exists = UserRequest::where([
            ['sender_id', Auth::user() -> id],
            ['user_id', $request -> friend_id]
        ]);

        if ($exists -> exists() != 0) {
            $exists -> delete();
            return response() -> json([
                'status' => 'success',
                'action' => 'delete',
                'message' => 'Add removed successfully'
            ], 200);
        }

        UserRequest::create([
            'user_id' => $request -> friend_id,
            'sender_id' => Auth::user() -> id
        ]);

        // event(new AddFriendEvent($request -> friend_id, 'Hello World'));

        return response() -> json([
            'status' => 'success',
            'action' => 'add',
            'message' => 'Add sent successfully'
        ], 201);

    }

    public function recents() {

        $recents = UserConversation::from('user_conversations as uc')
        -> select('uc.conversation_id', 'msg.content', 'msg.record', 'msg.read_users', 'msg.sent_at', 'msg.read_at', 'msg.files', 'u.name', 'c.title', 'c.type')
        -> join('user_conversations as uc2', function ($join) {
            $join -> on('uc.conversation_id', '=', 'uc2.conversation_id')
                  -> where('uc2.user_id', '=', Auth::user() -> id);
        })
        -> leftJoin(DB::raw('(SELECT * FROM messages m
                            JOIN (SELECT MAX(id) AS max_id
                                FROM messages
                                GROUP BY conversation_id) AS max_ids
                            ON max_ids.max_id = m.id) AS msg'), function($join) {
            $join -> on('uc.conversation_id', '=', 'msg.conversation_id');
        })
        -> leftJoin('users as u', 'u.id', '=', 'uc.user_id')
        -> leftJoin('conversations as c', 'c.id', '=', 'uc.conversation_id')
        -> where('uc.user_id', '!=', Auth::user() -> id)
        -> where('uc2.archived', 0)
        -> orderByDesc('msg.created_at')
        -> paginate(10);

        return response() -> json([
            'status' => 'success',
            'data' => $recents
        ]);

    }

    public function groups() {

        $groups = Conversation::from('conversations as c')
        -> select('c.id as conversation_id', 'c.title')
        -> join('user_conversations as uc', function ($join) {
            $join -> on('uc.conversation_id', '=', 'c.id')
                  -> where('uc.user_id', '=', Auth::user() -> id)
                  -> where('c.type', '=', 'group');
        })
        -> orderByDesc('uc.created_at')
        -> where('uc.archived', 0)
        -> paginate($this -> StanderPagenation);

        return response() -> json([
            'status' => 'success',
            'data' => $groups
        ]);

    }

    public function createGroup($request) {

        DB::beginTransaction();

        try {

            $conversation = Conversation::create([
                'title' => $request -> input('groupName'),
                'type' => 'group',
                'created_at' => now()
            ]);

            UserConversation::insert([
                ['user_id' => Auth::user() -> id, 'conversation_id' => $conversation -> id, 'owner' => 1, 'created_at' => now()]
            ]);

            DB::commit();

            return response() -> json([
                'status' => 'success',
                'conversation' => $conversation
            ]);

        } catch (\Throwable $th) {

            DB::rollback();

            return response() -> json([
                'status' => 'error',
                'message' => $th
            ]);

        }

    }

    public function friends() {

        $friends = Conversation::from('conversations as c')
        -> select('c.id as conversation_id', 'u.name')
        -> join('user_conversations as uc', function ($join) {
            $join -> on('uc.conversation_id', '=', 'c.id')
                  -> where('uc.user_id', '=', Auth::user() -> id);
        })
        -> join('user_conversations as uc2', function ($join) {
            $join -> on('uc2.conversation_id', '=', 'c.id')
                  -> where('uc2.user_id', '!=', Auth::user() -> id);
        })
        -> leftJoin('users as u', function ($join) {
            $join -> on('u.id', '=', 'uc2.user_id');
        })
        -> where('c.type', '=', 'direct_message')
        -> where('uc.archived', 0)
        -> orderByDesc('uc.created_at')
        -> paginate($this -> StanderPagenation);

        return response() -> json([
            'status' => 'success',
            'data' => $friends
        ]);

        // return new GroupsResource($groups);

        // return new GroupsResource();

    }

    public function GetArchived() {

        $archived = Conversation::from('conversations as c')
        -> select('c.id as conversation_id', 'u.name', 'c.title')
        -> leftJoin('user_conversations as uc', function ($join) {
            $join -> on('uc.conversation_id', '=', 'c.id')
                  -> where('uc.user_id', '=', Auth::user() -> id);
        })
        -> leftJoin('user_conversations as uc2', function ($join) {
            $join -> on('uc2.conversation_id', '=', 'c.id')
                  -> where('uc2.user_id', '!=', Auth::user() -> id);
        })
        -> leftJoin('users as u', function ($join) {
            $join -> on('u.id', '=', 'uc2.user_id');
        })
        -> orderByDesc('uc.created_at')
        -> where('uc.archived', 1)
        -> paginate($this -> StanderPagenation);

        return response() -> json([
            'status' => 'success',
            'data' => $archived
        ]);

        // return new GroupsResource($groups);

        // return new GroupsResource();

    }

    public function requests() {

        $friendsRequests = UserRequest::with(['user'])
                                        // -> withCount('commonUsers')
                                        -> where('user_id', Auth::user() -> id)
                                        -> where('status', '=', 'pending')
                                        -> paginate(1);

        return response() -> json([
            'status' => 'success',
            'data' => $friendsRequests
        ], 200);

    }

    public function sends() {

        $sendFriendsRequests = UserRequest::with(['user'])
                                        // -> withCount('commonUsers')
                                        -> where('sender_id', Auth::user() -> id)
                                        -> where('status', '=', 'pending')
                                        -> paginate(1);

        return response() -> json([
            'status' => 'success',
            'data' => $sendFriendsRequests
        ], 200);

    }

    public function response($request, $request_id) {

        DB::beginTransaction();

        try {

            $user_request = UserRequest::where('id', $request_id);

            $user_request -> update([
                'status' => $request -> status,
                'updated_at' => now()
            ]);

            $user_request_info = $user_request -> first();

            $conversation = Conversation::create([
                'title' => null,
                'type' => 'direct_message',
                'created_at' => now()
            ]);

            UserConversation::insert([
                ['user_id' => $user_request_info -> user_id, 'conversation_id' => $conversation -> id, 'owner' => 1, 'created_at' => now()],
                ['user_id' => $user_request_info -> sender_id, 'conversation_id' => $conversation -> id, 'owner' => 1, 'created_at' => now()]
            ]);

            DB::commit();

            return response() -> json([
                'status' => 'success',
                'message' => 'user ' . $request -> status
            ]);

        } catch (\Throwable $th) {

            DB::rollback();

            return response() -> json([
                'status' => 'error',
                'message' => $th
            ]);

        }

    }

    public function BlockUser($request, $chat_id) {

        $userExistsInConversation = Conversation::from('conversations as c')
            -> join('user_conversations as uc', function ($join) {
                $join -> on('uc.conversation_id', '=', 'c.id')
                    -> where('uc.user_id', '=', Auth::user() -> id);
            })
            -> where('c.id', $chat_id)
            -> first();

        if ($userExistsInConversation) {

            DB::beginTransaction();

            try {

                Conversation::where('id', $chat_id) -> delete();
                UserBlocks::create([
                    'block_user_id' => Auth::user() -> id,
                    'blocked_user_id' => $request -> user_id
                ]);

                DB::commit();
                return response() -> json([
                    'status' => 'success',
                    'message' => 'blocked successfully'
                ]);

            } catch (\Throwable $th) {

                DB::rollBack();

                return response() -> json([
                    'status' => 'error',
                    'message' => $th
                ]);

            }


        } else {

            return response() -> json([
                'status' => 'error',
                'message' => 'no chat found'
            ]);

        }

    }

    public function getSuggestion($request) {

        $usersInConversation = DB::table('user_conversations')
            -> where('conversation_id', $request -> input('chat_id'))
            -> where('user_id', '!=', Auth::user() -> id)
            -> pluck('user_id');

        $userRequests = UserRequest::select('users.id', 'name', 'email', 'photo')
        -> join('users', function ($join) {
            $join -> on('users.id', '=', 'user_requests.sender_id')
                -> where('user_requests.sender_id', '!=', Auth::user() -> id)
                -> orWhere(function ($query) {
                    $query -> on('users.id', '=', 'user_requests.user_id')
                        -> where('user_requests.user_id', '!=', Auth::user() -> id);
                });
        })
        -> where(function ($query) {
            $query -> where('user_requests.user_id', Auth::user() -> id)
                -> orWhere('user_requests.sender_id', Auth::user() -> id);
        })
        -> where('users.name', 'like', '%' . $request -> input('name') . '%')
        -> where('user_requests.status', 'approved')
        ->where(function ($query) use($usersInConversation) {
            $query->where(function ($query) use($usersInConversation) {
                $query->where('user_requests.sender_id', Auth::user() -> id)
                    ->whereNotIn('user_requests.user_id', $usersInConversation);
            })
            ->orWhere(function ($query) use($usersInConversation) {
                $query->where('user_requests.user_id', Auth::user() -> id)
                    ->whereNotIn('user_requests.sender_id', $usersInConversation);
            });
        })
        -> get();

        return response() -> json([
            'status' => 'success',
            'data' => $userRequests
        ]);

    }

}