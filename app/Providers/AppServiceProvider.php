<?php

namespace App\Providers;

use App\Models\Conversation;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        View::composer('*', function ($view) {
            if (Auth::check()) {
                $user = Auth::user();

                if (!$view->offsetExists('myConversations')) {
                    $myConversations = Conversation::where('user_id', $user->id)
                        ->orderByDesc('is_pinned')
                        ->orderByDesc('last_active_at')
                        ->withCount('messages')
                        ->get();
                    $view->with('myConversations', $myConversations);
                }

                if (!$view->offsetExists('sharedConversations')) {
                    $sharedConversations = Conversation::where('user_id', '!=', $user->id)
                        ->where(function ($q) use ($user) {
                            $q->where('visibility', 'team')
                              ->orWhereHas('shares', fn($s) => $s->where('user_id', $user->id));
                        })
                        ->orderByDesc('last_active_at')
                        ->withCount('messages')
                        ->get();
                    $view->with('sharedConversations', $sharedConversations);
                }
            }
        });
    }
}
