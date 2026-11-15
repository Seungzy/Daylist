<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DeleteMemberController;
use App\Http\Controllers\LevelController;
use App\Http\Controllers\MemberAccessController;
use App\Http\Controllers\MemberController;
use App\Http\Controllers\MemberPortalController;
use App\Http\Controllers\MembershipApplicationController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\PersonalTaskController;
use App\Http\Controllers\StatusController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\WeatherController;
use App\Http\Middleware\EnsureAdministrator;
use App\Http\Middleware\EnsureMember;
use Illuminate\Support\Facades\Route;

Route::view('/', 'home')->name('home');
Route::get('/about', [PageController::class, 'about']);
Route::get('/activity/tasks/{id}', [PageController::class, 'task'])->name('activity.task');
Route::get('/membership/apply', [MembershipApplicationController::class, 'create'])->name('membership.create');
Route::post('/membership/apply', [MembershipApplicationController::class, 'store'])->middleware('throttle:5,1')->name('membership.store');
Route::get('/membership/verify/{application}/{hash}', [MembershipApplicationController::class, 'verify'])->middleware(['signed', 'throttle:10,1'])->name('membership.verify');
Route::get('/weather', [WeatherController::class, 'show'])->middleware('throttle:30,1')->name('weather.show');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'create'])->name('login');
    Route::post('/login', [AuthController::class, 'store'])->name('login.store');
});
Route::post('/logout', [AuthController::class, 'destroy'])->middleware('auth')->name('logout');
Route::middleware(['auth', EnsureAdministrator::class])->group(function () {
    Route::resource('tasks', TaskController::class)->except('show');
    Route::patch('/tasks/{task}/completion', [TaskController::class, 'updateCompletion'])->name('tasks.completion');
    Route::get('/applications', [MembershipApplicationController::class, 'index'])->name('applications.index');
    Route::get('/applications/{application}', [MembershipApplicationController::class, 'show'])->name('applications.show');
    Route::post('/members/{member}/access', MemberAccessController::class)->name('members.access');
    Route::delete('/members/{member}/permanent', DeleteMemberController::class)->name('members.permanent-destroy');
    Route::get('/integrations', fn () => redirect()->route('status.index'))->name('integrations');
    Route::get('/status', [StatusController::class, 'index'])->name('status.index');
    Route::post('/status/smtp', [StatusController::class, 'smtp'])->middleware('throttle:3,1')->name('status.smtp');
    Route::get('/dashboard', DashboardController::class)->name('dashboard');
    Route::resource('members', MemberController::class);
    Route::patch('/members/{member}/reactivate', [MemberController::class, 'reactivate'])->name('members.reactivate');
    Route::resource('categories', CategoryController::class)->except(['create', 'show']);
    Route::resource('levels', LevelController::class)->except(['create', 'show']);
    Route::resource('transactions', TransactionController::class)->only(['index', 'create', 'store', 'show']);
    Route::patch('/transactions/{transaction}/approve', [TransactionController::class, 'approve'])->name('transactions.approve');
    Route::patch('/transactions/{transaction}/cancel', [TransactionController::class, 'cancel'])->name('transactions.cancel');
    Route::get('/payments', [PaymentController::class, 'index'])->name('payments.index');
    Route::get('/transactions/{transaction}/payments/create', [PaymentController::class, 'create'])->name('payments.create');
    Route::post('/transactions/{transaction}/payments', [PaymentController::class, 'store'])->name('payments.store');
    Route::get('/payments/{payment}', [PaymentController::class, 'show'])->name('payments.show');
});

Route::middleware(['auth', EnsureMember::class])->prefix('portal')->name('portal.')->group(function () {
    Route::get('/profile', [MemberPortalController::class, 'profile'])->name('profile');
    Route::get('/tasks', [MemberPortalController::class, 'tasks'])->name('tasks');
    Route::post('/tasks', [PersonalTaskController::class, 'store'])->name('tasks.store');
    Route::get('/tasks/{task}/edit', [PersonalTaskController::class, 'edit'])->name('tasks.edit');
    Route::put('/tasks/{task}', [PersonalTaskController::class, 'update'])->name('tasks.update');
    Route::patch('/tasks/{task}/completion', [PersonalTaskController::class, 'completion'])->name('tasks.completion');
    Route::delete('/tasks/{task}', [PersonalTaskController::class, 'destroy'])->name('tasks.destroy');
});
