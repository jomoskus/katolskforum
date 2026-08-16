<?php

declare(strict_types=1);

use App\Http\Controllers\PostController;
use App\Http\Controllers\ThreadController;
use Illuminate\Support\Facades\Route;

Route::get('/', [ThreadController::class, 'index'])->name('home');

Route::middleware(['auth', 'verified'])->group(function (): void {
    // Registered before threads.show so the literal "create" segment is
    // never captured by the {thread} slug parameter.
    Route::get('threads/create', [ThreadController::class, 'create'])->name('threads.create');
    Route::post('threads', [ThreadController::class, 'store'])->name('threads.store');
    Route::get('threads/{thread}/edit', [ThreadController::class, 'edit'])->name('threads.edit');
    Route::put('threads/{thread}', [ThreadController::class, 'update'])->name('threads.update');
    Route::delete('threads/{thread}', [ThreadController::class, 'destroy'])->name('threads.destroy');

    Route::post('threads/{thread}/posts', [PostController::class, 'store'])->name('threads.posts.store');
    Route::get('posts/{post}/edit', [PostController::class, 'edit'])->name('posts.edit');
    Route::put('posts/{post}', [PostController::class, 'update'])->name('posts.update');
    Route::delete('posts/{post}', [PostController::class, 'destroy'])->name('posts.destroy');
});

Route::get('threads/{thread}', [ThreadController::class, 'show'])->name('threads.show');

require __DIR__.'/settings.php';
