<?php

use App\Http\Controllers\PobController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserManagementController;
use Illuminate\Support\Facades\Route;

// Redirect root to planning
Route::get('/', function () {
    return redirect()->route('pob.planning');
});

// Protected routes
Route::middleware('auth')->group(function () {
    // POB Planning routes
    Route::get('/planning', [PobController::class, 'planning'])->name('pob.planning');
    Route::post('/planning', [PobController::class, 'storePlanning'])->name('pob.storePlanning');
    Route::patch('/planning/{planning}', [PobController::class, 'updatePlanning'])->name('pob.updatePlanning');
    Route::delete('/planning/{planning}', [PobController::class, 'destroyPlanning'])->name('pob.destroyPlanning');
    Route::get('/export', [PobController::class, 'export'])->name('pob.export');

    // Comparison
    Route::get('/comparison', [PobController::class, 'comparison'])->name('pob.comparison');

    // Admin - User Management (Super Admin only)
    Route::get('/admin/users', [UserManagementController::class, 'index'])->name('admin.users');
    Route::patch('/admin/users/{user}/approve', [UserManagementController::class, 'approve'])->name('admin.users.approve');
    Route::patch('/admin/users/{user}/revoke', [UserManagementController::class, 'revoke'])->name('admin.users.revoke');
    Route::patch('/admin/users/{user}/role', [UserManagementController::class, 'updateRole'])->name('admin.users.updateRole');
    Route::delete('/admin/users/{user}', [UserManagementController::class, 'destroy'])->name('admin.users.destroy');

    // Profile routes
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__ . '/auth.php';

