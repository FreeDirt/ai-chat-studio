<?php

use App\Http\Controllers\ChatController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\PersonaController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\PromptTemplateController;
use App\Http\Controllers\BookmarkController;
use App\Http\Controllers\SetupController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Middleware\SuperAdminMiddleware;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\ShareController;
use App\Http\Controllers\PresenceController;

// Public Shared Chat Route
Route::get('/share/{token}', [ShareController::class, 'viewPublicShare'])->name('share.public');

// Setup Wizard Routes
Route::prefix('setup')->name('setup.')->group(function () {
    Route::get('/',  [SetupController::class, 'index'])->name('index');
    Route::post('/', [SetupController::class, 'store'])->name('store');
});

// Authentication Routes
Route::get('/login',   [AuthController::class, 'showLogin'])->name('login');
Route::post('/login',  [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Authenticated Application Routes
Route::middleware(['auth'])->group(function () {
    Route::get('/', [ChatController::class, 'index'])->name('chat.index');

    // Conversation management
    Route::prefix('conversations')->name('conversations.')->group(function () {
        Route::get('/search',                             [ChatController::class, 'search'])->name('search');
        Route::post('/',                                  [ChatController::class, 'newConversation'])->name('new');
        Route::get('/{conversation}/messages',            [ChatController::class, 'getMessages'])->name('messages');
        Route::post('/send',                              [ChatController::class, 'sendMessage'])->name('send');
        Route::post('/stream',                            [ChatController::class, 'streamMessage'])->name('stream');
        Route::post('/{conversation}/regenerate',         [ChatController::class, 'regenerateMessage'])->name('regenerate');
        Route::patch('/messages/{message}',               [ChatController::class, 'editMessage'])->name('messages.edit');
        Route::delete('/messages/{message}',              [ChatController::class, 'deleteMessage'])->name('messages.delete');
        Route::delete('/{conversation}',                  [ChatController::class, 'deleteConversation'])->name('delete');
        Route::patch('/{conversation}/rename',            [ChatController::class, 'renameConversation'])->name('rename');
        Route::patch('/{conversation}/pin',               [ChatController::class, 'togglePin'])->name('pin');
        Route::get('/{conversation}/export',              [ChatController::class, 'export'])->name('export');
        Route::post('/compare',                           [ChatController::class, 'compare'])->name('compare');
        Route::post('/enhance-prompt',                    [ChatController::class, 'enhancePrompt'])->name('enhance-prompt');
        Route::post('/messages/{message}/branch',         [ChatController::class, 'branchMessage'])->name('messages.branch');
        Route::get('/messages/{message}/siblings',        [ChatController::class, 'getSiblings'])->name('messages.siblings');
        Route::get('/{conversation}/share',               [ShareController::class, 'getSettings'])->name('share.get');
        Route::post('/{conversation}/share',              [ShareController::class, 'updateSettings'])->name('share.update');
    });

    // Models & Analytics
    Route::get('/models', [ChatController::class, 'listModels'])->name('models.list');
    Route::get('/analytics', [ChatController::class, 'analytics'])->name('analytics');

    // Presence & Real-Time Collaboration
    Route::post('/presence/heartbeat',              [PresenceController::class, 'heartbeat'])->name('presence.heartbeat');
    Route::get('/presence/{conversation}',          [PresenceController::class, 'getPresence'])->name('presence.get');

    // Settings
    Route::prefix('settings')->name('settings.')->group(function () {
        Route::get('/',           [SettingsController::class, 'index'])->name('index');
        Route::post('/',          [SettingsController::class, 'update'])->name('update');
        Route::post('/test',      [SettingsController::class, 'testConnection'])->name('test');
        Route::get('/models',     [SettingsController::class, 'fetchModels'])->name('models');
    });

    // Personas
    Route::prefix('personas')->name('personas.')->group(function () {
        Route::get('/',                  [PersonaController::class, 'index'])->name('index');
        Route::post('/',                 [PersonaController::class, 'store'])->name('store');
        Route::post('/upload-icon',      [PersonaController::class, 'uploadIcon'])->name('upload-icon');
        Route::put('/{persona}',         [PersonaController::class, 'update'])->name('update');
        Route::delete('/{persona}',      [PersonaController::class, 'destroy'])->name('destroy');
        Route::post('/reorder',          [PersonaController::class, 'reorder'])->name('reorder');
    });

    // Documents (RAG)
    Route::prefix('documents')->name('documents.')->group(function () {
        Route::post('/upload',                        [DocumentController::class, 'upload'])->name('upload');
        Route::get('/conversation/{conversation}',    [DocumentController::class, 'list'])->name('list');
        Route::get('/persona/{persona}',              [DocumentController::class, 'listForPersona'])->name('persona.list');
        Route::delete('/{document}',                  [DocumentController::class, 'destroy'])->name('destroy');
    });

    // Prompt Templates (Library)
    Route::prefix('prompt-templates')->name('prompt-templates.')->group(function () {
        Route::get('/',                      [PromptTemplateController::class, 'index'])->name('index');
        Route::post('/',                     [PromptTemplateController::class, 'store'])->name('store');
        Route::delete('/{promptTemplate}',   [PromptTemplateController::class, 'destroy'])->name('destroy');
    });

    // Bookmarks
    Route::prefix('bookmarks')->name('bookmarks.')->group(function () {
        Route::get('/',               [BookmarkController::class, 'index'])->name('index');
        Route::post('/',              [BookmarkController::class, 'store'])->name('store');
        Route::delete('/{bookmark}',  [BookmarkController::class, 'destroy'])->name('destroy');
    });

    // Super Admin Routes
    Route::prefix('admin')->name('admin.')->middleware([SuperAdminMiddleware::class])->group(function () {
        Route::get('/users',                    [UserController::class, 'index'])->name('users.index');
        Route::post('/users',                   [UserController::class, 'store'])->name('users.store');
        Route::put('/users/{user}',             [UserController::class, 'update'])->name('users.update');
        Route::delete('/users/{user}',          [UserController::class, 'destroy'])->name('users.destroy');
        Route::patch('/users/{user}/role',      [UserController::class, 'toggleRole'])->name('users.toggle-role');
        Route::patch('/users/{user}/status',    [UserController::class, 'toggleStatus'])->name('users.toggle-status');

        // Workspace Branding Studio
        Route::get('/branding',                 [\App\Http\Controllers\Admin\BrandingController::class, 'index'])->name('branding.index');
        Route::post('/branding',                [\App\Http\Controllers\Admin\BrandingController::class, 'update'])->name('branding.update');
    });
});
