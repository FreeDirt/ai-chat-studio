<?php

namespace App\Http\Controllers;

use App\Models\Bookmark;
use Illuminate\Http\Request;

class BookmarkController extends Controller
{
    /**
     * Get list of bookmarks.
     */
    public function index(Request $request)
    {
        $query = Bookmark::with('conversation')->latest();

        if ($request->filled('conversation_id')) {
            $query->where('conversation_id', $request->conversation_id);
        }

        $bookmarks = $query->get()->map(fn($b) => [
            'id'              => $b->id,
            'title'           => $b->title,
            'content'         => $b->content,
            'tags'            => $b->tags,
            'conversation_id' => $b->conversation_id,
            'message_id'      => $b->message_id,
            'created_at'      => $b->created_at->diffForHumans(),
        ]);

        return response()->json(['success' => true, 'bookmarks' => $bookmarks]);
    }

    /**
     * Store a bookmark.
     */
    public function store(Request $request)
    {
        $request->validate([
            'content'         => 'required|string|max:65535',
            'title'           => 'nullable|string|max:255',
            'conversation_id' => 'nullable|exists:conversations,id',
            'message_id'      => 'nullable|exists:messages,id',
            'tags'            => 'nullable|string|max:255',
        ]);

        $title = $request->title;
        if (empty($title)) {
            $title = \Illuminate\Support\Str::limit(strip_tags($request->content), 40);
        }

        $bookmark = Bookmark::create([
            'conversation_id' => $request->conversation_id,
            'message_id'      => $request->message_id,
            'title'           => $title,
            'content'         => $request->content,
            'tags'            => $request->tags,
        ]);

        return response()->json([
            'success'  => true,
            'message'  => 'Snippet bookmarked successfully!',
            'bookmark' => $bookmark,
        ], 201);
    }

    /**
     * Delete a bookmark.
     */
    public function destroy(Bookmark $bookmark)
    {
        $bookmark->delete();

        return response()->json([
            'success' => true,
            'message' => 'Bookmark removed.',
        ]);
    }
}
