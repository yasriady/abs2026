<?php

use App\Http\Controllers\AbsensiHarianController;
use App\Http\Controllers\Admin\PegawaiHistorisRawController;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Devel\BaseDevelController;
use App\Http\Controllers\Devel\ValidDeviceController;
use App\Http\Controllers\DevelController;
use App\Http\Controllers\MahasiswaController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UnitController;
use App\Http\Controllers\SubUnitController;
use App\Http\Controllers\DeviceController;
use App\Http\Controllers\HariLiburNasionalController;
use App\Http\Controllers\PegawaiController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\V1\PegawaiController as V1PegawaiController;
use App\Http\Controllers\VerifikasiAbsensiController;
use App\Http\Controllers\HarianController;
use App\Http\Controllers\HarianControllerX;
use App\Http\Controllers\PegawaiFotoController;

require __DIR__ . '/auth.php';

Route::get('/pegawai/{pegawai}/histori', [PegawaiController::class, 'histori'])
    ->name('pegawai.histori');
Route::get('/pegawai/{pegawai}', [PegawaiController::class, 'show'])
    ->name('pegawai.show');
Route::get('/pegawai/{pegawai}/edit-master', [PegawaiController::class, 'editMaster'])
    ->name('pegawai.edit-master');
Route::put('/pegawai/{pegawai}/update-master', [PegawaiController::class, 'updateMaster'])
    ->name('pegawai.update-master');
Route::get('/pegawai/{pegawai}/histori/{history}/edit', [PegawaiController::class, 'editHistori'])
    ->name('pegawai.histori.edit');
Route::put('/pegawai/{pegawai}/histori/{history}', [PegawaiController::class, 'updateHistori'])
    ->name('pegawai.histori.update');
// Raw edit routes
Route::get(
    '/pegawai/{pegawai}/histori/{history}/raw-edit',
    [PegawaiController::class, 'editHistoriRaw']
)->name('pegawai.histori.raw-edit');
Route::put(
    '/pegawai/{pegawai}/histori/{history}/raw-update',
    [PegawaiController::class, 'updateHistoriRaw']
)->name('pegawai.histori.raw-update');

Route::prefix('admin')->group(function () {
    Route::get('/pegawai-historis-raw', [PegawaiHistorisRawController::class, 'index'])
        ->name('pegawai-historis-raw.index');

    Route::put('/pegawai-historis-raw/{pegawaiHistorisRaw}', [PegawaiHistorisRawController::class, 'update'])
        ->name('pegawai-historis-raw.update');
    // ---------------------Autofix -- -----------------------
    Route::post(
        '/pegawai-historis-raw/{pegawai}/auto-fix',
        [PegawaiHistorisRawController::class, 'autoFix']
    )->name('pegawai-historis-raw.auto-fix');
});

// Route::get('/pegawai/foto/{nik}', function ($nik) {
//     return xpegawai_foto_response($nik);
// })->name('pegawai.foto');

// Route::get('/pegawai/foto/{nik}', [\App\Http\Controllers\PegawaiFotoController::class, 'show'])
//     ->name('pegawai.foto')
//     ->middleware('auth');
Route::get('/pegawai/foto/{id}', [PegawaiFotoController::class, 'show'])
    ->name('pegawai.foto')
    ->middleware('auth');
Route::get('/absensi/foto/{inOut}/{sum_id}', [PegawaiFotoController::class, 'absensiFoto'])
    ->name('absensi.foto.in')
    ->middleware('auth');

Route::middleware('auth')->group(function () {

    Route::get('/dashboard', function () {
        return Inertia::render('Dashboard');
    })->name('dashboard');

    // Route::get('/unit', [UnitController::class, 'index']);
    // Route::post('/unit', [UnitController::class, 'store']);
    // Route::put('/unit/{unit}', [UnitController::class, 'update']);
    // Route::delete('/unit/{unit}', [UnitController::class, 'destroy']);

    // --- DEVICE ROUTES WITH PERMISSION MIDDLEWARE ---

    Route::get('/device', [DeviceController::class, 'index'])
        ->middleware('permission:perangkat.view');

    Route::post('/device', [DeviceController::class, 'store'])
        ->middleware('permission:perangkat.create');

    Route::put('/device/{device}', [DeviceController::class, 'update'])
        ->middleware('permission:perangkat.update');

    Route::delete('/device/{device}', [DeviceController::class, 'destroy'])
        ->middleware('permission:perangkat.delete');

    // --- UNIT ROUTES WITH PERMISSION MIDDLEWARE ---
    Route::prefix('unit')->group(function () {
        Route::get('/', [UnitController::class, 'index'])
            ->middleware('permission:unit.view');

        Route::post('/', [UnitController::class, 'store'])
            ->middleware('permission:unit.create');

        Route::put('/{unit}', [UnitController::class, 'update'])
            ->middleware('permission:unit.update');

        Route::delete('/{unit}', [UnitController::class, 'destroy'])
            ->middleware('permission:unit.delete');
    });

    // --- SUBUNIT ROUTES WITH PERMISSION MIDDLEWARE ---
    Route::prefix('subunit')->group(function () {
        Route::get('/', [SubUnitController::class, 'index'])
            ->middleware('permission:subunit.view');

        Route::post('/', [SubUnitController::class, 'store'])
            ->middleware('permission:subunit.create');

        Route::put('/{unit}', [SubUnitController::class, 'update'])
            ->middleware('permission:subunit.update');

        Route::delete('/{unit}', [SubUnitController::class, 'destroy'])
            ->middleware('permission:subunit.delete');
    });

    // --- PEGAWAI ROUTES WITH PERMISSION MIDDLEWARE ---
    Route::prefix('pegawai')->group(function () {
        Route::get('/', [PegawaiController::class, 'index'])
            ->middleware('permission:pegawai.view');

        Route::post('/', [PegawaiController::class, 'store'])
            ->middleware('permission:pegawai.create');

        Route::put('/{pegawai}', [PegawaiController::class, 'update'])
            ->middleware('permission:pegawai.update');

        Route::delete('/{pegawai}', [PegawaiController::class, 'destroy'])
            ->middleware('permission:pegawai.delete');
    });

    // --- PEGAWAI ROUTES WITH PERMISSION MIDDLEWARE ---
    Route::prefix('v1/pegawai')->group(function () {
        Route::get('/', [V1PegawaiController::class, 'index'])
            ->name('v1.pegawai.index')
            ->middleware('permission:pegawai.view');
    });

    Route::prefix('v1')->middleware(['auth'])->group(function () {
        Route::get('/pegawai/{pegawai}/edit', [\App\Http\Controllers\V1\PegawaiController::class, 'edit'])
            ->name('pegawai.edit');

        Route::put('/pegawai/{pegawai}', [\App\Http\Controllers\V1\PegawaiController::class, 'update'])
            ->name('pegawai.update');
    });



    // --- HARI-LIBUR-NASIONAL ROUTES WITH PERMISSION MIDDLEWARE ---
    Route::prefix('hari-libur-nasional')->group(function () {
        Route::get('/', [HariLiburNasionalController::class, 'index'])
            ->middleware('permission:libur.view');

        Route::post('/', [HariLiburNasionalController::class, 'store'])
            ->middleware('permission:libur.create');

        Route::put('/{hariLiburNasional}', [HariLiburNasionalController::class, 'update'])
            ->middleware('permission:libur.update');

        Route::delete('/{hariLiburNasional}', [HariLiburNasionalController::class, 'destroy'])
            ->middleware('permission:libur.delete');
    });


    Route::get('/verifikasi-absensi', [VerifikasiAbsensiController::class, 'index'])
        ->name('verifikasi-absensi.index');







    // Route::get('/subunit', [SubUnitController::class, 'index']);
    // Route::post('/subunit', [SubUnitController::class, 'store']);
    // Route::put('/subunit/{subUnit}', [SubUnitController::class, 'update']);
    // Route::delete('/subunit/{subUnit}', [SubUnitController::class, 'destroy']);




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


    Route::get('/migrasi-pegawai', [PegawaiController::class, 'migrasiPegawai']);
    Route::get('/migrasi-pegawai-fast', [PegawaiController::class, 'migrasiPegawaiFast']);
    Route::get('/devel', [ValidDeviceController::class, 'index']);

    // x_
    Route::get('/absensi-harian', [HarianControllerX::class, 'index'])
        ->name('absensi-harian.index');

    // x_
    Route::get('/absensi-harian-new', [AbsensiHarianController::class, 'index'])
        ->name('absensi.harian-new');

    Route::get('/absensi-harian', [AbsensiHarianController::class, 'index'])
        ->name('absensi.harian');

    /* ===============================
        UPDATE STATUS HARIAN
        =============================== */
    Route::post(
        '/absensi/update-status',
        [AbsensiHarianController::class, 'updateStatus']
    )->name('absensi.updateStatus');
    // Route::post('/absensi/update-status', [AbsensiHarianController::class, 'updateStatus']);

    /* ===============================
       UPDATE JAM MASUK / PULANG
    =============================== */
    Route::post(
        '/absensi/update-jam',
        [AbsensiHarianController::class, 'updateJam']
    )->name('absensi.updateJam');

    // Route::post('/absensi/regenerate-unit', [AbsensiHarianController::class, 'regenerateUnit'])
    //     ->name('absensi.regenerate-unit');

    Route::post('/absensi/regenerate-nik', [AbsensiHarianController::class, 'regenerateNik'])
        ->name('absensi.regenerate.nik');
    // Route::post('/absensi/regenerate-single', [AbsensiHarianController::class, 'regenerateNik']);
    Route::get('/absensi/regenerate-single-status/{id}', [AbsensiHarianController::class, 'statusSingle']);
});

Route::middleware(['auth', 'permission:user.view'])->get('/user', [UserController::class, 'index']);
Route::middleware(['auth', 'permission:user.create'])->post('/user', [UserController::class, 'store']);
Route::middleware(['auth', 'permission:user.update'])->put('/user/{user}', [UserController::class, 'update']);
Route::middleware(['auth', 'permission:user.delete'])->delete('/user/{user}', [UserController::class, 'destroy']);
