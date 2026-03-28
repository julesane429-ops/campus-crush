<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SwipeController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\MatchController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\SubscriptionController;
use Illuminate\Support\Facades\Route;

// ── Public ──
Route::get('/', [HomeController::class, 'index'])->name('home');

// ── Auth ──
Route::middleware('guest')->group(function () {
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

// ── Webhook PayDunya (pas de middleware auth, appelé par PayDunya) ──
Route::post('/webhook/paydunya', [SubscriptionController::class, 'webhook'])->name('webhook.paydunya');

// ── Routes auth (sans abonnement requis) ──
Route::middleware('auth')->group(function () {

    // Profil
    Route::get('/profile/create', [ProfileController::class, 'create'])->name('profile.create');
    Route::post('/profile/store', [ProfileController::class, 'store'])->name('profile.store');
    Route::get('/me', [ProfileController::class, 'show'])->name('profile.show');
    Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Abonnement
    Route::get('/subscription', [SubscriptionController::class, 'index'])->name('subscription.index');
    Route::post('/subscription/pay', [SubscriptionController::class, 'pay'])->name('subscription.pay');
    Route::get('/subscription/success', [SubscriptionController::class, 'success'])->name('subscription.success');
    Route::get('/subscription/cancel', [SubscriptionController::class, 'cancel'])->name('subscription.cancel');

    // Settings
    Route::get('/settings', fn() => view('settings.index'))->name('settings');
    Route::get('/settings/notifications', fn() => view('settings.notifications'))->name('settings.notifications');
    Route::get('/settings/privacy', fn() => view('settings.privacy'))->name('settings.privacy');

    // Notifications API
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllRead']);
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markRead']);
});

// ── Routes avec abonnement requis ──
Route::middleware(['auth', 'subscription'])->group(function () {

    // Swipe
    Route::get('/swipe', [SwipeController::class, 'index'])->name('swipe');
    Route::post('/like/{id}', [SwipeController::class, 'like'])->where('id', '[0-9]+');
    Route::post('/pass/{id}', [SwipeController::class, 'pass'])->where('id', '[0-9]+');
    Route::get('/load-profiles', [SwipeController::class, 'loadMoreProfiles']);
    Route::get('/nav-counts', [SwipeController::class, 'navCounts']);

    // Matchs
    Route::get('/matches', [MatchController::class, 'index'])->name('matches');
    Route::get('/match/{id}', [MatchController::class, 'show'])->name('match.show')->where('id', '[0-9]+');

    // Messages
    Route::get('/messages/{match}', [MessageController::class, 'show'])->name('messages.chat')->where('match', '[0-9]+');
    Route::post('/messages/{match}', [MessageController::class, 'send'])->name('messages.send')->where('match', '[0-9]+');
    Route::post('/messages/{match}/block', [MessageController::class, 'block'])->name('messages.block');
    Route::post('/messages/{match}/report', [MessageController::class, 'report'])->name('messages.report');
    Route::delete('/messages/{match}/delete', [MessageController::class, 'delete'])->name('messages.delete');
    Route::post('/typing/{match}', [MessageController::class, 'typing'])->where('match', '[0-9]+');
});

// ── Panel Admin ──
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminController::class, 'dashboard'])->name('dashboard');
    Route::get('/users', [AdminController::class, 'users'])->name('users');
    Route::post('/users/{id}/ban', [AdminController::class, 'banUser'])->name('users.ban');
    Route::post('/users/{id}/unban', [AdminController::class, 'unbanUser'])->name('users.unban');
    Route::delete('/users/{id}', [AdminController::class, 'deleteUser'])->name('users.delete');
    Route::get('/reports', [AdminController::class, 'reports'])->name('reports');
    Route::post('/reports/{id}/resolve', [AdminController::class, 'resolveReport'])->name('reports.resolve');
    Route::get('/payments', [AdminController::class, 'payments'])->name('payments');
});
