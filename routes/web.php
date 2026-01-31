<?php

use App\Http\Controllers\MahasiswaController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UnitController;
use App\Http\Controllers\SubUnitController;

require __DIR__ . '/auth.php';

Route::middleware('auth')->group(function () {

    Route::get('/dashboard', function () {
        return Inertia::render('Dashboard');
    })->name('dashboard');

    Route::get('/unit', [UnitController::class, 'index']);
    Route::post('/unit', [UnitController::class, 'store']);
    Route::put('/unit/{unit}', [UnitController::class, 'update']);
    Route::delete('/unit/{unit}', [UnitController::class, 'destroy']);

    Route::get('/subunit', [SubUnitController::class, 'index']);
    Route::post('/subunit', [SubUnitController::class, 'store']);
    Route::put('/subunit/{subUnit}', [SubUnitController::class, 'update']);
    Route::delete('/subunit/{subUnit}', [SubUnitController::class, 'destroy']);



    Route::get('/mahasiswa', [MahasiswaController::class, 'index']);
    Route::post('/mahasiswa', [MahasiswaController::class, 'store']);
    Route::put('/mahasiswa/{mahasiswa}', [MahasiswaController::class, 'update']);
    Route::delete('/mahasiswa/{mahasiswa}', [MahasiswaController::class, 'destroy']);
    Route::get('/mahasiswa/export/excel', [MahasiswaController::class, 'exportExcel']);
    Route::get('/mahasiswa/export/pdf', [MahasiswaController::class, 'exportPdf']);

    // ROUTE PROFILE (WAJIB untuk Breeze + Ziggy)
    Route::get('/profile', [ProfileController::class, 'edit'])
        ->name('profile.edit');

    Route::patch('/profile', [ProfileController::class, 'update'])
        ->name('profile.update');

    Route::delete('/profile', [ProfileController::class, 'destroy'])
        ->name('profile.destroy');

    // Optional: redirect root ke dashboard
    Route::get('/', function () {
        return redirect()->route('dashboard');
    });
});
