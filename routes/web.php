<?php

use App\Http\Controllers\Admin\WorkspaceController as AdminWorkspaceController;
use App\Http\Controllers\AppearanceController;
use App\Http\Controllers\Auth\AdminAuthController;
use App\Http\Controllers\Auth\ConfirmPasswordController;
use App\Http\Controllers\Auth\ResidentAuthController;
use App\Http\Controllers\Auth\StaffAuthController;
use App\Http\Controllers\ComplaintCommentController;
use App\Http\Controllers\ComplaintController;
use App\Http\Controllers\ComplaintEvidenceController;
use App\Http\Controllers\ComplaintWorkflowController;
use App\Http\Controllers\LandingController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Resident\DashboardController as ResidentDashboardController;
use App\Http\Controllers\Staff\WorkspaceController as StaffWorkspaceController;
use Illuminate\Support\Facades\Route;

Route::get('/', [LandingController::class, 'index'])->name('home');

Route::middleware('guest:resident')->group(function (): void {
    Route::get('/login', [ResidentAuthController::class, 'login'])->name('login');
    Route::post('/login', [ResidentAuthController::class, 'storeLogin'])->middleware('throttle:role-login')->name('login.store');
    Route::get('/register', [ResidentAuthController::class, 'register'])->name('register');
    Route::post('/register', [ResidentAuthController::class, 'storeRegistration'])->middleware('throttle:5,1')->name('register.store');
    Route::get('/forgot-password', [ResidentAuthController::class, 'forgotPassword'])->name('password.request');
    Route::post('/forgot-password', [ResidentAuthController::class, 'sendResetLink'])->middleware('throttle:5,1')->name('password.email');
    Route::get('/reset-password/{token}', [ResidentAuthController::class, 'resetPasswordForm'])->name('password.reset');
    Route::post('/reset-password', [ResidentAuthController::class, 'resetPassword'])->middleware('throttle:5,1')->name('password.update');
});

Route::post('/logout', [ResidentAuthController::class, 'logout'])->middleware('auth:resident')->name('logout');
Route::get('/user/confirm-password', [ConfirmPasswordController::class, 'show'])->middleware('auth:resident')->name('password.confirm');
Route::post('/user/confirm-password', [ConfirmPasswordController::class, 'store'])->middleware(['auth:resident', 'throttle:6,1'])->name('password.confirm.store');
Route::get('/dashboard', ResidentDashboardController::class)
    ->middleware(['auth:resident', 'role:resident'])
    ->name('dashboard');

Route::prefix('resident')->name('resident.')->middleware(['auth:resident', 'role:resident'])->group(function (): void {
    Route::get('/dashboard', ResidentDashboardController::class)->name('dashboard');
    Route::get('/complaints', [ComplaintController::class, 'index'])->name('complaints.index');
    Route::get('/complaints/create', [ComplaintController::class, 'create'])->name('complaints.create');
    Route::post('/complaints', [ComplaintController::class, 'store'])->name('complaints.store');
    Route::get('/complaints/{complaint}', [ComplaintController::class, 'show'])->name('complaints.show');
    Route::post('/complaints/{complaint}/comments', [ComplaintCommentController::class, 'store'])->name('complaints.comments.store');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
});

Route::get('/staff/login', [StaffAuthController::class, 'login'])->middleware('guest:staff')->name('staff.login');
Route::post('/staff/login', [StaffAuthController::class, 'storeLogin'])->middleware(['guest:staff', 'throttle:role-login'])->name('staff.login.store');
Route::post('/staff/logout', [StaffAuthController::class, 'logout'])->middleware('auth:staff')->name('staff.logout');
Route::prefix('staff')->name('staff.')->middleware(['auth:staff', 'role:staff'])->group(function (): void {
    Route::get('/dashboard', [StaffWorkspaceController::class, 'dashboard'])->name('dashboard');
    Route::get('/complaints', [StaffWorkspaceController::class, 'complaints'])->name('complaints.index');
    Route::get('/assigned', [StaffWorkspaceController::class, 'assigned'])->name('assigned');
    Route::get('/complaints/{complaint}', [ComplaintController::class, 'show'])->name('complaints.show');
    Route::post('/complaints/{complaint}/comments', [ComplaintCommentController::class, 'store'])->name('complaints.comments.store');
    Route::post('/complaints/{complaint}/approve', [ComplaintWorkflowController::class, 'approve'])->name('complaints.approve');
    Route::post('/complaints/{complaint}/reject', [ComplaintWorkflowController::class, 'reject'])->name('complaints.reject');
    Route::post('/complaints/{complaint}/resolve', [ComplaintWorkflowController::class, 'resolve'])->name('complaints.resolve');
    Route::get('/residents', [StaffWorkspaceController::class, 'residents'])->name('residents.index');
    Route::get('/reports', [StaffWorkspaceController::class, 'reports'])->name('reports');
    Route::get('/settings', [StaffWorkspaceController::class, 'settings'])->name('settings');
    Route::patch('/settings', [StaffWorkspaceController::class, 'updateSettings'])->name('settings.update');
});

Route::get('/admin/login', [AdminAuthController::class, 'login'])->middleware('guest:admin')->name('admin.login');
Route::post('/admin/login', [AdminAuthController::class, 'storeLogin'])->middleware(['guest:admin', 'throttle:role-login'])->name('admin.login.store');
Route::post('/admin/logout', [AdminAuthController::class, 'logout'])->middleware('auth:admin')->name('admin.logout');
Route::prefix('admin')->name('admin.')->middleware(['auth:admin', 'role:admin'])->group(function (): void {
    Route::get('/dashboard', [AdminWorkspaceController::class, 'dashboard'])->name('dashboard');
    Route::get('/users', [AdminWorkspaceController::class, 'users'])->name('users.index');
    Route::post('/users', [AdminWorkspaceController::class, 'storeUser'])->name('users.store');
    Route::patch('/users/{user}', [AdminWorkspaceController::class, 'updateUser'])->name('users.update');
    Route::patch('/users/{user}/access', [AdminWorkspaceController::class, 'toggleUser'])->name('users.access');
    Route::get('/complaints', [AdminWorkspaceController::class, 'complaints'])->name('complaints.index');
    Route::get('/complaints/{complaint}', [ComplaintController::class, 'show'])->name('complaints.show');
    Route::post('/complaints/{complaint}/comments', [ComplaintCommentController::class, 'store'])->name('complaints.comments.store');
    Route::post('/complaints/{complaint}/approve', [ComplaintWorkflowController::class, 'approve'])->name('complaints.approve');
    Route::post('/complaints/{complaint}/reject', [ComplaintWorkflowController::class, 'reject'])->name('complaints.reject');
    Route::post('/complaints/{complaint}/resolve', [ComplaintWorkflowController::class, 'resolve'])->name('complaints.resolve');
    Route::get('/reports', [AdminWorkspaceController::class, 'reports'])->name('reports');
    Route::get('/reports/export.csv', [AdminWorkspaceController::class, 'exportCsv'])->name('reports.csv');
    Route::get('/reports/export.pdf', [AdminWorkspaceController::class, 'exportPdf'])->name('reports.pdf');
    Route::get('/information', [AdminWorkspaceController::class, 'information'])->name('information');
    Route::patch('/information', [AdminWorkspaceController::class, 'updateInformation'])->name('information.update');
    Route::get('/activity', [AdminWorkspaceController::class, 'activity'])->name('activity');
    Route::get('/settings', [AdminWorkspaceController::class, 'settings'])->name('settings');
    Route::patch('/settings', [AdminWorkspaceController::class, 'updateSettings'])->name('settings.update');
});

Route::get('/complaint-evidence/{evidence}', [ComplaintEvidenceController::class, 'show'])
    ->middleware('auth:resident,staff,admin')
    ->name('complaint-evidence.show');

Route::patch('/appearance', [AppearanceController::class, 'update'])->middleware('auth:resident,staff,admin')->name('appearance.update');

require __DIR__.'/settings.php';
