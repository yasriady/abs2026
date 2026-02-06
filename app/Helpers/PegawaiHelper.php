<?php

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;

if (!function_exists('xpegawai_foto_response')) {
    function xpegawai_foto_response(string $nik)
    {
        $url = "http://192.10.10.2/penduduk/foto/{$nik}.jpg";

        try {
            $response = Http::timeout(3)->get($url);

            if (!$response->successful()) {
                abort(404);
            }

            return response($response->body(), 200)
                ->header('Content-Type', 'image/jpeg')
                ->header('Cache-Control', 'public, max-age=86400');
        } catch (\Throwable $e) {
            abort(404);
        }
    }
}

if (!function_exists('ppegawai_foto_response')) {
    function ppegawai_foto_response(string $nik)
    {
        return Cache::remember("pegawai_foto_{$nik}", 86400, function () use ($nik) {

            $defaultImage = public_path('images/default-user.png');

            // kalau default image tidak ada → stop (config error)
            if (!File::exists($defaultImage)) {
                abort(500, 'Default image not found');
            }

            // kalau NIK kosong → langsung fallback
            if (empty($nik)) {
                return response()->file($defaultImage, [
                    'Cache-Control' => 'public, max-age=86400',
                ]);
            }

            try {
                $url = "http://192.10.10.2/penduduk/foto/{$nik}.jpg";

                $response = Http::timeout(3)->get($url);

                // kalau foto ditemukan
                if ($response->successful()) {
                    return response($response->body(), 200)
                        ->header('Content-Type', 'image/jpeg')
                        ->header('Cache-Control', 'public, max-age=86400');
                }
            } catch (\Throwable $e) {
                // sengaja dikosongkan → fallback
            }

            // =========================
            // FALLBACK DEFAULT IMAGE
            // =========================
            return response()->file($defaultImage, [
                'Cache-Control' => 'public, max-age=86400',
            ]);
        });
    }
}
