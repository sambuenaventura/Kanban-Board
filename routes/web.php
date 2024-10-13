<?php

use App\Http\Controllers\BoardController;
use App\Http\Controllers\BoardUserController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PricingController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SocialAuthController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\TaskController;
use App\Mail\WelcomeMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Route;
use Laravel\Socialite\Facades\Socialite;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::get('/auth/redirect', function() {
    return Socialite::driver('github')->redirect();
})->middleware('throttle:10,1');  // 10 requests per minute

Route::get('/auth/callback', [SocialAuthController::class, 'handleProviderCallback'])->middleware('throttle:10,1');  // 10 requests per minute for the callback

// 60 requests per minute
Route::middleware(['auth', 'throttle:60,1'])->group(function () {
    // Board user management and invitations routes
    Route::get('/boards/invitations', [BoardUserController::class, 'manageInvitations'])->name('boards.manageInvitations');
    Route::post('/boards/{board}/invite', [BoardUserController::class, 'inviteUserToBoard'])->name('boards.inviteUser');
    Route::post('/boards/invitations/{invitation}/accept', [BoardUserController::class, 'acceptInvitation'])->name('boards.acceptInvitation');
    Route::post('/boards/invitations/{invitation}/decline', [BoardUserController::class, 'declineInvitation'])->name('boards.declineInvitation');
    
    Route::delete('/boards/{board}/invitations/{invitation}', [BoardUserController::class, 'cancelInvitation'])->name('boards.cancelInvitation');

    Route::post('/boards/{board}/add-user', [BoardUserController::class, 'addUserToBoard'])->name('boards.addUser');
    Route::delete('/boards/{board}/remove-user/{user}', [BoardUserController::class, 'removeUserFromBoard'])->name('boards.removeUser');

    // Board routes
    Route::get('/boards', [BoardController::class, 'index'])->name('boards.index');
    Route::get('/boards/create', [BoardController::class, 'create'])->name('boards.create');
    Route::post('/boards', [BoardController::class, 'store'])->name('boards.store');
    Route::get('/boards/{id}', [BoardController::class, 'show'])->name('boards.show');
    Route::get('/boards/{id}/edit', [BoardController::class, 'edit'])->name('boards.edit');
    Route::put('/boards/{id}', [BoardController::class, 'update'])->name('boards.update');
    Route::delete('/boards/{id}', [BoardController::class, 'destroy'])->name('boards.destroy');

    // Task routes under a specific board
    Route::get('/boards/{boardId}/tasks', [TaskController::class, 'index'])->name('boards.tasks.index');
    Route::get('/boards/{boardId}/tasks/create', [TaskController::class, 'create'])->name('boards.tasks.create');
    Route::post('/boards/{boardId}/tasks', [TaskController::class, 'store'])->name('boards.tasks.store');
    Route::get('/boards/{boardId}/tasks/{taskId}', [TaskController::class, 'show'])->name('boards.tasks.show');
    Route::get('/boards/{boardId}/tasks/{taskId}/edit', [TaskController::class, 'edit'])->name('boards.tasks.edit');
    Route::patch('/boards/{boardId}/tasks/{taskId}', [TaskController::class, 'update'])->name('boards.tasks.update');
    Route::delete('/boards/{boardId}/tasks/{taskId}', [TaskController::class, 'destroy'])->name('boards.tasks.destroy');

    // Task-specific actions
    Route::delete('/tasks/{id}/remove', [TaskController::class, 'remove'])->name('tasks.remove');       
    Route::patch('/tasks/{taskId}/status', [TaskController::class, 'updateStatus'])->name('tasks.updateStatus');
    Route::post('/tasks/{task}/files', [TaskController::class, 'uploadFile'])->name('tasks.uploadFile');
    Route::delete('/tasks/{task}/{attachment}/files', [TaskController::class, 'destroyFile'])->name('tasks.destroyFile');
});

Route::middleware(['auth'])->group(function () {
    Route::get('/pricing', [PricingController::class, 'index'])->name('pricing.index');
    Route::get('/checkout/{plan?}', CheckoutController::class)->name('checkout');
    Route::post('/subscription/change', [SubscriptionController::class, 'change'])->name('subscription.change');
    Route::post('/subscription/cancel', [SubscriptionController::class, 'cancel'])->name('subscription.cancel');
   
});

Route::middleware(['auth'])->group(function () {
    Route::get('send-email',[TaskController::class, "sendEmail"]);
    Route::get('/testroute', [NotificationController::class, 'sendEmail']);
});


Route::get('/auth/callback', [SocialAuthController::class, 'handleProviderCallback']);

require __DIR__.'/auth.php';
