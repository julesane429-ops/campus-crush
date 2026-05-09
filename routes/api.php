<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\SwipeController;
use App\Http\Controllers\Api\MatchController;
use App\Http\Controllers\Api\LikeController;
use App\Http\Controllers\Api\SubscriptionController;
use App\Http\Controllers\Api\BoostController;
use App\Http\Controllers\Api\CrushController;
use App\Http\Controllers\Api\AiChatController;
use App\Http\Controllers\Api\ReferralController;
use App\Http\Controllers\Api\ReviewController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\SettingsController;
use App\Http\Controllers\Api\AdminController;
use App\Http\Controllers\Api\PushController;
use Illuminate\Support\Facades\Route;

// ── Authentification (public) ────────────────────────────────────────────
Route::post('/auth/register', [AuthController::class, 'register'])->middleware('throttle:5,1');
Route::post('/auth/login',    [AuthController::class, 'login'])->middleware('throttle:5,1');

// ── Webhook PayDunya (public, appelé par PayDunya) ───────────────────────
Route::post('/webhook/paydunya', [\App\Http\Controllers\SubscriptionController::class, 'webhook']);

// ── Profil public ────────────────────────────────────────────────────────
Route::get('/u/{slug}', [SettingsController::class, 'publicProfile']);

// ── Routes authentifiées ─────────────────────────────────────────────────
Route::middleware(['auth:sanctum', 'banned'])->group(function () {

    // Auth
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/auth/me',      [AuthController::class, 'me']);

    // Push notifications (Expo)
    Route::post('/push/register',   [PushController::class, 'register']);
    Route::post('/push/unregister', [PushController::class, 'unregister']);

    // Statut en ligne
    Route::get('/user/{id}/status', [SettingsController::class, 'userStatus']);

    // Notifications
    Route::get('/notifications',              [NotificationController::class, 'index']);
    Route::post('/notifications/read-all',    [NotificationController::class, 'markAllRead']);
    Route::post('/notifications/{id}/read',   [NotificationController::class, 'markRead']);

    // Profil (sans abonnement requis pour créer/modifier)
    Route::get('/profile',    [ProfileController::class, 'show']);
    Route::post('/profile',   [ProfileController::class, 'store']);
    Route::put('/profile',    [ProfileController::class, 'update']);
    Route::delete('/profile', [ProfileController::class, 'destroy']);
    Route::get('/profile/user/{id}', [ProfileController::class, 'showUser'])->where('id', '[0-9]+');
    Route::get('/universities', [ProfileController::class, 'universities']);

    // Abonnement
    Route::get('/subscription',              [SubscriptionController::class, 'index']);
    Route::post('/subscription/pay',         [SubscriptionController::class, 'pay']);
    Route::get('/subscription/check/{token}', [SubscriptionController::class, 'check']);

    // Paramètres
    Route::get('/settings',                       [SettingsController::class, 'index']);
    Route::put('/settings/notifications',         [SettingsController::class, 'updateNotifications']);
    Route::put('/settings/privacy',               [SettingsController::class, 'updatePrivacy']);

    // Avis
    Route::get('/review',    [ReviewController::class, 'show']);
    Route::post('/review',   [ReviewController::class, 'store']);
    Route::delete('/review', [ReviewController::class, 'destroy']);

    // Parrainage
    Route::get('/referral', [ReferralController::class, 'index']);

    // ── Routes avec abonnement requis ────────────────────────────────────
    Route::middleware('subscription')->group(function () {

        // Swipe
        Route::get('/swipe/profiles',     [SwipeController::class, 'profiles']);
        Route::post('/swipe/like/{id}',   [SwipeController::class, 'like'])->where('id', '[0-9]+');
        Route::post('/swipe/pass/{id}',   [SwipeController::class, 'pass'])->where('id', '[0-9]+');
        Route::delete('/swipe/undo/{id}', [SwipeController::class, 'undo'])->where('id', '[0-9]+');
        Route::get('/swipe/nav-counts',   [SwipeController::class, 'navCounts']);

        // Matchs & Messages
        Route::get('/matches',                          [MatchController::class, 'index']);
        Route::get('/matches/{id}/messages',            [MatchController::class, 'messages'])->where('id', '[0-9]+');
        Route::post('/matches/{id}/messages',           [MatchController::class, 'send'])->where('id', '[0-9]+');
        Route::post('/matches/{id}/typing',             [MatchController::class, 'typing'])->where('id', '[0-9]+');
        Route::post('/matches/{id}/block',              [MatchController::class, 'block'])->where('id', '[0-9]+');
        Route::post('/matches/{id}/report',             [MatchController::class, 'report'])->where('id', '[0-9]+');
        Route::delete('/matches/{id}',                  [MatchController::class, 'destroy'])->where('id', '[0-9]+');

        // Likes
        Route::get('/likes', [LikeController::class, 'whoLikedMe']);

        // Boost
        Route::get('/boost',      [BoostController::class, 'index']);
        Route::post('/boost/pay', [BoostController::class, 'pay']);

        // Crush anonyme
        Route::get('/crush',              [CrushController::class, 'index']);
        Route::post('/crush/send',        [CrushController::class, 'send']);
        Route::post('/crush/{id}/reveal', [CrushController::class, 'reveal'])->where('id', '[0-9]+');

        // IA Chat
        Route::get('/ai/bots',                    [AiChatController::class, 'bots']);
        Route::get('/ai/chat/{botType}',          [AiChatController::class, 'session']);
        Route::post('/ai/chat/{sessionId}/send',  [AiChatController::class, 'send'])->where('sessionId', '[0-9]+');
        Route::post('/ai/chat/{sessionId}/reset', [AiChatController::class, 'reset'])->where('sessionId', '[0-9]+');
        Route::post('/ai/unlock/pay',             [AiChatController::class, 'pay']);
    });
});

// ── Admin (auth:sanctum + is_admin) ─────────────────────────────────────
Route::middleware(['auth:sanctum', 'admin'])->prefix('admin')->group(function () {
    Route::get('/dashboard',           [AdminController::class, 'dashboard']);
    Route::get('/users',               [AdminController::class, 'users']);
    Route::post('/users/{id}/ban',     [AdminController::class, 'banUser'])->where('id', '[0-9]+');
    Route::post('/users/{id}/unban',   [AdminController::class, 'unbanUser'])->where('id', '[0-9]+');
    Route::delete('/users/{id}',       [AdminController::class, 'deleteUser'])->where('id', '[0-9]+');
    Route::get('/reports',             [AdminController::class, 'reports']);
    Route::post('/reports/{id}/resolve', [AdminController::class, 'resolveReport'])->where('id', '[0-9]+');
    Route::get('/payments',            [AdminController::class, 'payments']);
    Route::get('/analytics',           [AdminController::class, 'analytics']);
    Route::get('/reviews',             [AdminController::class, 'reviews']);
    Route::post('/reviews/{id}/approve', [AdminController::class, 'approveReview'])->where('id', '[0-9]+');
    Route::post('/reviews/{id}/reject',  [AdminController::class, 'rejectReview'])->where('id', '[0-9]+');
    Route::post('/reviews/{id}/feature', [AdminController::class, 'featureReview'])->where('id', '[0-9]+');
    Route::delete('/reviews/{id}',       [AdminController::class, 'deleteReview'])->where('id', '[0-9]+');
});
