<?php

namespace App\Repository\Chats;

// Classes
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

// Interface
use App\Repository\Chats\ChatsRepoInterface;

// Models
use App\Models\UserRequest;
use App\Models\Conversation;
use App\Models\UserConversation;
use App\Models\Message;

// Resources
use App\Http\Resources\Chat\MessageResource;

// Events
use App\Events\SendMessageEvent;

class ChatsRepo implements ChatsRepoInterface {

    public function getChat($chat_id) {

        $conversation = Conversation::with(['members' => function ($query) {
                $query -> with('user') -> where('user_id', '!=', Auth::user() -> id);
            }])
            -> from('conversations as c')
            -> select('c.*', 'u.name', 'uc.user_id', 'uc_me.owner', 'uc_me.archived')
            -> leftJoin('user_conversations as uc', function ($join) {
                $join -> on('uc.conversation_id', '=', 'c.id')
                    -> where('uc.user_id', '!=', Auth::user() -> id);
            })
            -> leftJoin('user_conversations as uc_me', function ($join) {
                $join -> on('uc_me.conversation_id', '=', 'c.id')
                    -> where('uc_me.user_id', '=', Auth::user() -> id);
            })
            -> leftJoin('users as u', 'u.id', '=', 'uc.user_id')
            -> where('c.id', $chat_id)
            -> first();

        $userExistsInConversation = UserConversation::where('conversation_id', $chat_id)
                                                    -> where('user_id', Auth::user() -> id)
                                                    -> first();

        if ($conversation && $userExistsInConversation) {

            $user_id = Auth::user() -> id;

            Message::where('conversation_id', $chat_id)
                    -> where('sender_id', '!=', Auth::user() -> id)
                    -> whereRaw('NOT FIND_IN_SET(?, read_users) OR FIND_IN_SET(?, read_users) IS null', [Auth::user() -> id, Auth::user() -> id])
                    -> update([
                        'read_users' => DB::raw("CONCAT(IFNULL(read_users, ''), ',{$user_id}')")
                    ]);

            $messages = Message::with('user:id,name')
                                -> select(DB::raw('DATE(sent_at) as sent_at'), DB::raw('sent_at as sent_at_original'), DB::raw('DATE_FORMAT(sent_at, "%H:%i") AS hour_and_minute'), 'content', 'record', 'files', 'sender_id', 'read_users', 'read_at', 'conversation_id')
                                -> where('conversation_id', $chat_id)
                                -> orderBy('sent_at_original', 'desc')
                                -> paginate(150);

            $messagesCollection = collect($messages -> items());
            $groupedMessages = $messagesCollection -> groupBy('sent_at');

            $transformedMessages = [];
            foreach ($groupedMessages as $day => $DayMessages) {

                $transformedMessages[$day][] = [
                    $DayMessages[0]['sender_id'] => [
                        $DayMessages[0]
                    ]
                ];

                foreach ($DayMessages as $msgIndex => $msg) {

                    if ($msgIndex > 0) {

                        if (isset(end($transformedMessages[$day])[$msg -> sender_id])) {
                            $transformedMessages[$day][count((array) $transformedMessages[$day]) - 1][$msg -> sender_id][] = $msg;
                        } else {

                            $transformedMessages[$day][] = [
                                $msg['sender_id'] => [
                                    $msg
                                ]
                            ];

                        }
                    }

                }

            }

            $data = [
                'data' => $transformedMessages,
                'total' => $messages -> total(),
                'count' => $messages -> count(),
                'per_page' => $messages -> perPage(),
                'current_page' => $messages -> currentPage(),
                'last_page' => $messages -> lastPage()
            ];

            $conversation -> messages = $data;
            $conversation -> files = $messages;

            return response() -> json([
                'status' => 'success',
                'data' => $conversation
            ]);

        } else {

            return response() -> json([
                'status' => 'error',
                'message' => 'Not data found'
            ]);

        }

    }

    public function sendMessage($request, $chat_id) {

        if (request() -> hasFile('record')) {

            $msg = null;
            $files = null;
            $record = Storage::disk('attachements') -> put('chats/' . $chat_id . '/records', request() -> file('record'));

        } else if (request() -> input('files')) {

            $FileNum = 1;
            $FilesSoted = [];
            while (request() -> file('file_' . $FileNum)) {
                $storedPath = Storage::disk('attachements') -> put('chats/' . $chat_id . '/files', request() -> file('file_' . $FileNum));
                $FilesSoted[] = $storedPath;
                $FileNum++;
            }

            $record = null;
            $files = json_encode($FilesSoted);
            $msg = json_decode(request() -> input('content'));

        } else {

            $files = null;
            $record = null;
            $msg = json_decode(request() -> input('content'));

        }

        $anotherFriend = UserConversation::select('user_id')
                                        -> where('conversation_id', $chat_id)
                                        -> where('user_id', '!=', Auth::user() -> id)
                                        -> first();

        // Create function
        $message = Message::create([
            'content' => $msg,
            'record' => $record,
            'conversation_id' => $chat_id,
            'sender_id' => Auth::user() -> id,
            'sent_at' => now(),
            'files' => $files
        ]);

        $sent_at = Carbon::parse(now()) -> format('Y-m-d');
        $message['sent_at'] = $sent_at;
        $message['sender_id'] = Auth::user() -> id;

        SendMessageEvent::dispatch($anotherFriend -> user_id, $message);

        return new MessageResource($message);

    }

    public function deleteChat($chat_id) {

        $userExistsInConversationAndOwner = Conversation::from('conversations as c')
            -> join('user_conversations as uc', function ($join) {
                $join -> on('uc.conversation_id', '=', 'c.id')
                    -> where('uc.user_id', '=', Auth::user() -> id)
                    -> where('uc.owner', '=', 1);
            })
            -> where('c.id', $chat_id)
            -> first();

        if ($userExistsInConversationAndOwner) {

            Message::where('conversation_id', $chat_id) -> delete();

            return response() -> json([
                'status' => 'success',
                'message' => 'deleted successfully'
            ]);

        } else {

            return response() -> json([
                'status' => 'error',
                'message' => 'no chat found'
            ]);

        }

    }
    
    public function archiveChat($request, $chat_id) {
        
        $userExistsInConversation = Conversation::from('conversations as c')
        -> join('user_conversations as uc', function ($join) {
            $join -> on('uc.conversation_id', '=', 'c.id')
                    -> where('uc.user_id', '=', Auth::user() -> id);
            })
            -> where('c.id', $chat_id)
            -> first();

        if ($userExistsInConversation) {

            UserConversation::where('conversation_id', $chat_id)
                            -> where('user_id', Auth::user() -> id)
                            -> update([
                                'archived' => $request -> archived
                            ]);

            

            return response() -> json([
                'status' => 'success',
                'message' => 'archived successfully'
            ]);

        } else {

            return response() -> json([
                'status' => 'error',
                'message' => 'no chat found'
            ]);

        }

    }

    public function addMember($request) {

        $userFriendAndExistsInConversation = UserRequest::from('user_requests as ur')
            -> join('user_conversations as uc', function($join) use($request) {
                // $join -> on('uc.user_id', 'ur.user_id')
                //         -> orWhere('uc.user_id', 'ur.sender_id')
                $join->on(function($query) {
                    $query->where('uc.user_id', '=', 'ur.user_id')
                        ->orWhere('uc.user_id', '=', 'ur.sender_id');
                })
                -> where(function($query) use($request) {
                    $query -> where('uc.user_id', $request -> input("member_id"))
                            -> where('uc.conversation_id', $request -> input('chat_id'));
                });
            })
            -> where(function ($query) use($request) {
                $query -> where(function($query) use($request) {
                    $query -> where('ur.user_id', Auth::user() -> id)
                            -> where('ur.sender_id', $request -> input('member_id'));
                })
                -> orWhere(function($query) use($request) {
                    $query -> where('ur.sender_id', Auth::user() -> id)
                            -> where('ur.user_id', $request -> input('member_id'));
                });
            })
            -> where('ur.status', 'approved')
            -> count();

        if ($userFriendAndExistsInConversation == 0) {

            UserConversation::create([
                'user_id' => $request -> input('member_id'),
                'conversation_id' => $request -> input('chat_id'),
                'owner' => 0,
                'created_at' => now()
            ]);

            return response() -> json([
                'status' => 'success'
            ], 201);

        } else {
            
            return response() -> json([
                'status' => 'error',
                'message' => 'Invalid data'
            ]);

        }

    }

    public function removeMember($chat_id, $memberID) {

        $memberInChat = UserConversation::where('user_id', $memberID) -> where('conversation_id', $chat_id);
        $memberExistsInChat = $memberInChat -> count();

        $authedUserOwnerInChat = UserConversation::where('user_id', Auth::user() -> id)
                                                    -> where('owner', 1)
                                                    -> count();

        if ($memberExistsInChat > 0 && $authedUserOwnerInChat > 0) {

            $memberInChat -> delete();

            return response() -> json([
                'status' => 'success',
                'message' => 'deleted successfully'
            ]);

        }

    }

}