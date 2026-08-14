<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Models\ConversationShare;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ShareController extends Controller
{
    /**
     * Get share settings for a conversation.
     */
    public function getSettings(Conversation $conversation)
    {
        $this->authorizeAccess($conversation);

        $users = User::where('id', '!=', auth()->id())->select('id', 'name', 'email')->get();
        $shares = $conversation->shares()->pluck('permission', 'user_id')->toArray();

        return response()->json([
            'success'     => true,
            'visibility'  => $conversation->visibility ?? 'private',
            'share_token' => $conversation->share_token,
            'share_url'   => url('/share/' . $conversation->share_token),
            'users'       => $users->map(fn($u) => [
                'id'         => $u->id,
                'name'       => $u->name,
                'email'      => $u->email,
                'permission' => $shares[$u->id] ?? null,
            ]),
        ]);
    }

    /**
     * Update share settings for a conversation.
     */
    public function updateSettings(Request $request, Conversation $conversation)
    {
        $this->authorizeAccess($conversation);

        $request->validate([
            'visibility' => 'required|in:private,team,link,custom',
            'shares'     => 'nullable|array',
            'shares.*.user_id'    => 'required|exists:users,id',
            'shares.*.permission' => 'required|in:view,edit',
        ]);

        if (empty($conversation->share_token)) {
            $conversation->share_token = Str::random(32);
        }

        $conversation->visibility = $request->visibility;
        $conversation->save();

        // Update custom user shares
        $conversation->shares()->delete();

        if ($request->visibility === 'custom' && !empty($request->shares)) {
            foreach ($request->shares as $shareData) {
                ConversationShare::create([
                    'conversation_id' => $conversation->id,
                    'user_id'         => $shareData['user_id'],
                    'permission'      => $shareData['permission'],
                ]);
            }
        }

        return response()->json([
            'success'    => true,
            'message'    => 'Sharing settings updated!',
            'visibility' => $conversation->visibility,
            'share_url'  => url('/share/' . $conversation->share_token),
        ]);
    }

    /**
     * View public/shared conversation via share token.
     */
    public function viewPublicShare($token)
    {
        $conversation = Conversation::where('share_token', $token)->firstOrFail();

        if ($conversation->visibility === 'private' && (!auth()->check() || $conversation->user_id !== auth()->id())) {
            abort(403, 'This conversation is private.');
        }

        $messages = $conversation->messages()->get();

        return view('chat.public_share', compact('conversation', 'messages'));
    }

    private function authorizeAccess(Conversation $conversation)
    {
        if ($conversation->user_id && $conversation->user_id !== auth()->id() && !auth()->user()->isSuperAdmin()) {
            abort(403, 'Unauthorized');
        }
    }
}
