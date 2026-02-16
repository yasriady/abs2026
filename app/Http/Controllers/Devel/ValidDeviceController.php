<?php

namespace App\Http\Controllers\Devel;

use App\Http\Controllers\Controller;
use App\Models\AbsensiSummary;
use App\Models\DailyNote;
use App\Models\TappingNote;
use App\Services\JadwalResolverService;
use Illuminate\Http\Request;

class ValidDeviceController extends Controller
{
    public function index()
    {
        // $absensi = AbsensiSummary::find(5351); //12823 RiTam at 20251203, Sargun , peg puskesmas 11473, fadel 5351, defi 3944
        // $dailyNote = $absensi?->resolveDailyNote();

        // $absensi = AbsensiSummary::find(5351);
        // return $absensi;

        // dd([
        //     'absensi'   => $absensi,
        //     'dailyNote' => $absensi?->resolveDailyNote(),
        // ]);

        $absensi = AbsensiSummary::find(5351);
        return response()->json($absensi);
    }

    private function validLoc()
    {
        // 1. Gunakan dynamic ID atau parameter method
        $absensi = AbsensiSummary::find(38543); //12823 RiTam at 20251203, 
        // Menggunakan RAND() langsung di query
        // $absensi = AbsensiSummary::whereNotNull('time_in')
        //     ->whereNotNull('time_out')
        //     ->orderByRaw('RAND()')
        //     ->first();

        // 2. Validasi data exists
        if (!$absensi) {
            return response()->json([
                'success' => false,
                'message' => 'Data absensi tidak ditemukan'
            ], 404);
        }

        // 3. Gunakan array untuk menyimpan response
        $response = [];

        // 4. Validasi device masuk
        // if ($absensi->valid_device_in) {
        //     $response['device_masuk'] = 'VALID';
        // } else {
        //     $response['device_masuk'] = 'INVALID atau belum absen masuk';
        // }
        if ($absensi->valid_device_in) {
            $response['device_masuk'] = 'VALID';
        } elseif (!$absensi->device_id_in) {
            $response['device_masuk'] = 'belum absen masuk';
        } else {
            $response['device_masuk'] = 'INVALID';
        }

        // 5. Validasi device keluar
        // if ($absensi->valid_device_out) {
        //     $response['device_keluar'] = 'VALID';
        // } else {
        //     $response['device_keluar'] = 'INVALID atau belum absen keluar';
        // }
        if ($absensi->valid_device_out) {
            $response['device_keluar'] = 'VALID';
        } elseif (!$absensi->device_id_out) {
            $response['device_keluar'] = 'belum absen keluar';
        } else {
            $response['device_keluar'] = 'INVALID';
        }

        // 6. Status lengkap masuk
        if ($absensi->time_in) {
            if ($absensi->valid_device_in) {
                $response['status_masuk'] = 'Sudah absen masuk dengan device yang valid';
            } else {
                $response['status_masuk'] = 'Sudah absen masuk tapi device tidak valid';
            }
        } else {
            $response['status_masuk'] = 'Belum absen masuk';
        }

        // 7. Status lengkap keluar
        if ($absensi->time_out) {
            if ($absensi->valid_device_out) {
                $response['status_keluar'] = 'Sudah absen keluar dengan device yang valid';
            } else {
                $response['status_keluar'] = 'Sudah absen keluar tapi device tidak valid';
            }
        } else {
            $response['status_keluar'] = 'Belum absen keluar';
        }

        // 8. Return sebagai JSON response (untuk API) atau view
        return response()->json([
            'success' => true,
            'data' => $absensi,
            'validations' => $response
        ]);
    }

    // Helper methods untuk memisahkan logic
    private function getStatusMasuk($absensi)
    {
        if (!$absensi->time_in) {
            return 'Belum absen masuk';
        }

        return $absensi->valid_device_in
            ? 'Sudah absen masuk dengan device yang valid'
            : 'Sudah absen masuk tapi device tidak valid';
    }

    private function getStatusKeluar($absensi)
    {
        if (!$absensi->time_out) {
            return 'Belum absen keluar';
        }

        return $absensi->valid_device_out
            ? 'Sudah absen keluar dengan device yang valid'
            : 'Sudah absen keluar tapi device tidak valid';
    }
}
