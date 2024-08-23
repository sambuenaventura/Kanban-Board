<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SocialAuthController;
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
});

Route::get('/auth/callback', [SocialAuthController::class, 'handleProviderCallback']);


Route::middleware('auth')->group(function () {

    Route::resource('tasks', TaskController::class); 
    // Route::put('/tasks/{task}', [TaskController::class, 'update'])->name('tasks.update');
    Route::patch('/tasks/{task}/status', [TaskController::class, 'updateStatus']);
    Route::delete('/tasks/{task}', [TaskController::class, 'destroy'])->name('tasks.destroy');
    // Route::delete('/tasks/{task}/delete', [TaskController::class, 'deleteTask'])->name('tasks.deleteTask');

    Route::get('/tasks/tag/{tags?}', [TaskController::class, 'index']);
    Route::get('/tasks/priority/{priorities?}', [TaskController::class, 'index']);
    
    Route::get('/tasks/{task}', [TaskController::class, 'show'])->name('tasks.show');
    Route::post('/tasks/{task}/edit', [TaskController::class, 'edit'])->name('tasks.edit');
    
    Route::post('/tasks/{task}', [TaskController::class, 'update'])->name('tasks.update');
    // Route::patch('/tasks/{task}', [TaskController::class, 'updateProgress'])->name('tasks.updateProgress');
    
    Route::post('/tasks/{task}/files', [TaskController::class, 'uploadFile'])->name('tasks.uploadFile');
    Route::delete('/tasks/{task}/files/{attachment}', [TaskController::class, 'destroyFile'])->name('tasks.destroyFile');

});



Route::get('send-email',[TaskController::class, "sendEmail"]);

Route::get('/testroute', function() {
    $name = "Funny coder";

    Mail::to('ismokecarrots@gmail.com')->send(new WelcomeMail($name));
});

    // Route::get('/notes', [NoteController::class, 'index'])->name('notes.index');
    // Route::get('/notes/create', [NoteController::class, 'create'])->name('notes.create');
    // Route::post('/notes', [NoteController::class, 'store'])->name('notes.store');
    // Route::get('/notes/{note}', [NoteController::class, 'show'])->name('notes.show');
    // Route::get('/notes/{note}/edit', [NoteController::class, 'edit'])->name('notes.edit');
    // Route::put('/notes/{note}', [NoteController::class, 'update'])->name('notes.update');
    // Route::delete('/notes/{note}', [NoteController::class, 'destroy'])->name('notes.destroy');

require __DIR__.'/auth.php';
