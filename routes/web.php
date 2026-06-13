<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AdminExportController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\HandoverController;
use App\Http\Controllers\ProfileController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/', [ItemController::class, 'index'])->name('home');
Route::get('/search', [ItemController::class, 'search'])->name('items.search');
Route::get('/resolved', [ItemController::class, 'resolved'])->name('items.resolved');
Route::get('/items/{item:slug}', [ItemController::class, 'show'])->name('items.show');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.store');
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->name('register.store');
    Route::get('/auth/google', [AuthController::class, 'googleRedirect'])->name('google.redirect');
    Route::get('/auth/google/callback', [AuthController::class, 'googleCallback'])->name('google.callback');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/items/create/new', [ItemController::class, 'create'])->name('items.create');
    Route::post('/items', [ItemController::class, 'store'])->name('items.store');
    Route::get('/handovers', [HandoverController::class, 'index'])->name('handovers.index');
    Route::post('/items/{item}/handover', [HandoverController::class, 'store'])->name('handovers.store');
    Route::patch('/handovers/{handover}/approve', [HandoverController::class, 'approve'])->name('handovers.approve');
    Route::patch('/handovers/{handover}/reject', [HandoverController::class, 'reject'])->name('handovers.reject');
    Route::patch('/handovers/{handover}/confirm', [HandoverController::class, 'confirm'])->name('handovers.confirm');
    Route::patch('/handovers/{handover}/cancel', [HandoverController::class, 'cancel'])->name('handovers.cancel');
    Route::get('/chat', [ChatController::class, 'index'])->name('chat.index');
    Route::post('/chat/start/{item}', [ChatController::class, 'start'])->name('chat.start');
    Route::get('/chat/{conversation}', [ChatController::class, 'show'])->name('chat.show');
    Route::post('/chat/{conversation}', [ChatController::class, 'send'])->name('chat.send');
    Route::get('/notifications', fn (Request $request) => view('notifications', ['notifications' => $request->user()->notifications()->latest()->paginate(20)]))->name('notifications');
    Route::post('/notifications/read', function (Request $request) { $request->user()->unreadNotifications->markAsRead(); return back(); })->name('notifications.read');
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::get('/admin', [AdminController::class, 'index'])->name('admin');
    Route::get('/admin/export/pdf', [AdminExportController::class, 'pdf'])->name('admin.export.pdf');
    Route::get('/admin/export/excel', [AdminExportController::class, 'excel'])->name('admin.export.excel');
    Route::get('/admin/export/csv', [AdminExportController::class, 'csv'])->name('admin.export.csv');
    Route::patch('/admin/items/{item}/spam', [AdminController::class, 'spam'])->name('admin.items.spam');
    Route::delete('/admin/items/{item}', [AdminController::class, 'destroy'])->name('admin.items.destroy');
});
