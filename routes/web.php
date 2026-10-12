<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ContractController;
use App\Http\Controllers\ContractDocumentController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Guest routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.attempt');
});

// Authenticated routes
Route::middleware('auth')->group(function () {
    Route::get('/', function () {
        return redirect()->route('contracts.index');
    });

    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Export CSV
    Route::get('/contracts/export/csv', [ContractController::class, 'exportCsv'])->name('contracts.export.csv');

    // Contracts CRUD & Workflows
    Route::get('/contracts', [ContractController::class, 'index'])->name('contracts.index');
    
    // Staff-only routes
    Route::middleware('role:staff')->group(function () {
        Route::get('/contracts/create', [ContractController::class, 'create'])->name('contracts.create');
        Route::post('/contracts', [ContractController::class, 'store'])->name('contracts.store');
        Route::get('/contracts/{contract}/edit', [ContractController::class, 'edit'])->name('contracts.edit');
        Route::put('/contracts/{contract}', [ContractController::class, 'update'])->name('contracts.update');
    });

    Route::get('/contracts/{contract}', [ContractController::class, 'show'])->name('contracts.show');

    // Supervisor-only routes
    Route::middleware('role:supervisor')->group(function () {
        Route::patch('/contracts/{contract}/approve', [ContractController::class, 'approve'])->name('contracts.approve');
        Route::patch('/contracts/{contract}/reject', [ContractController::class, 'reject'])->name('contracts.reject');
        Route::delete('/contracts/{contract}', [ContractController::class, 'destroy'])->name('contracts.destroy');
    });

    // Secure Document routes
    Route::get('/documents/{document}/download', [ContractDocumentController::class, 'download'])->name('documents.download');
    Route::get('/documents/{document}/preview', [ContractDocumentController::class, 'preview'])->name('documents.preview');
});
