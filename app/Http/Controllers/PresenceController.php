<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Models\User;
use Illuminate\Http\Request;

class PresenceController extends Controller
{
    /**
     * Heartbeat ping sent periodically by active browser clients.
     */
    public function heartbeat(Request $request)
    {
        $request->validate([
            'conversation_id' => 'nullable|exists:conversations,id',
            'is_typing'       => 'nullable|boolean',
        ]);

        $user = auth()->user();
        if ($user) {
            $user->update([
                'last_seen_at'           => now(),
                'active_conversation_id' => $request->conversation_id ?: null,
                'is_typing'              => $request->boolean('is_typing', false),
            ]);
        }

        return response()->json(['success' => true]);
    }

    /**
     * Fetch active presence data & online users for a conversation.
     */
    public function getPresence(Conversation $conversation)
    {
        if (!$conversation->isAccessibleBy(auth()->user())) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Active users in this conversation (seen in last 15s)
        $onlineUsers = User::where('active_conversation_id', $conversation->id)
            ->where('last_seen_at', '>=', now()->subSeconds(15))
            ->get(['id', 'name', 'email', 'role', 'is_typing', 'last_seen_at']);

        // Owner details
        $owner = $conversation->user ? [
            'id'    => $conversation->user->id,
            'name'  => $conversation->user->name,
            'email' => $conversation->user->email,
            'role'  => $conversation->user->role,
        ] : null;

        // Latest messages for real-time sync
        $latestMessages = $conversation->messages()
            ->with('user:id,name,role')
            ->orderBy('created_at', 'asc')
            ->get()
            ->map(fn($m) => [
                'id'               => $m->id,
                'role'             => $m->role,
                'content'          => $m->content,
                'model'            => $m->model,
                'tokens_used'      => $m->tokens_used,
                'response_time_ms' => $m->formatted_response_time,
                'author_name'      => $m->user?->name ?? ($m->role === 'user' ? 'User' : 'AI'),
                'created_at'       => $m->created_at->format('H:i'),
            ]);

        $typingUsers = $onlineUsers
            ->where('is_typing', true)
            ->map(fn($u) => $u->id === auth()->id() ? 'You' : $u->name)
            ->values()
            ->toArray();

        return response()->json([
            'success'        => true,
            'owner'          => $owner,
            'visibility'     => $conversation->visibility,
            'online_users'   => $onlineUsers,
            'typing_users'   => $typingUsers,
            'message_count'  => $conversation->messages()->count(),
            'messages'       => $latestMessages,
        ]);
    }
}
