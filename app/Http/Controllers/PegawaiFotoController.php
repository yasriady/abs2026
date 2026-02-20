<?php

namespace App\Http\Controllers;

use App\Models\MasterPegawai;
use Illuminate\Support\Facades\Http;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class PegawaiFotoController extends Controller
{
    public function show(string $id)
    {
        $pegawai = MasterPegawai::find($id);

        if (!$pegawai || !$pegawai->nik) {
            return $this->placeholderPegawai();
        }

        $url = "http://192.10.10.2/penduduk/foto/{$pegawai->nik}.jpg";

        try {
            $response = Http::timeout(3)->get($url);

            if (!$response->successful()) {
                return $this->placeholderPegawai();
            }

            $manager = new ImageManager(new Driver());
            $image = $manager->read($response->body());

            return response(
                $image->toJpeg(85),
                200,
                ['Content-Type' => 'image/jpeg']
            );
        } catch (\Throwable $e) {
            return $this->placeholderPegawai();
        }
    }

    private function placeholderPegawai()
    {
        $manager = new ImageManager(new Driver());
        $image = $manager->create(200, 250);
        $image->fill('#e2e8f0');

        $image->text('NO PHOTO', 100, 125, function ($font) {
            $font->size(20);
            $font->color('#64748b');
            $font->align('center');
            $font->valign('middle');
        });

        return response(
            $image->toJpeg(80),
            200,
            ['Content-Type' => 'image/jpeg']
        );
    }


    public function absensiFoto(string $inOut, string $sum_id)
    {
        try {
            // Validasi input
            if (!in_array($inOut, ['in', 'out'])) {
                abort(400, "Parameter inOut harus 'in' atau 'out'");
            }

            if (empty($sum_id)) {
                abort(400, "ID summary tidak boleh kosong");
            }

            // Cari data summary
            $summary = \App\Models\AbsensiSummary::find($sum_id);

            if (!$summary) {
                abort(404, "Data absensi summary tidak ditemukan");
            }

            // Tentukan filename berdasarkan jenis (in/out)
            $filename = $inOut === 'in' ? $summary->filename_in : $summary->filename_out;

            if (empty($filename)) {
                return $this->showPlaceholderImage($inOut);
            }


            // Ekstrak yyyymmdd dari filename
            // Format: NIK_yyyymmdd_HHMMSS.jpg
            preg_match('/_(\d{8})_/', $filename, $matches);

            if (!isset($matches[1])) {
                abort(400, "Format filename tidak valid");
            }
            $yyyymmdd = $matches[1];

            // Bangun URL lengkap
            $base_url = env('APP_UPLOAD_URL', 'http://192.10.10.2/smartmadani');
            $url = "{$base_url}/uploaded_photos/{$yyyymmdd}/{$filename}";

            // Ambil gambar dari URL
            $response = Http::timeout(5)->get($url);

            if (!$response->successful()) {
                // Coba alternatif URL jika ada (optional)
                $alternate_url = "http://192.10.10.2/smartmadani/uploaded_photos/{$yyyymmdd}/{$filename}";
                $response = Http::timeout(3)->get($alternate_url);

                if (!$response->successful()) {
                    return $this->showPlaceholderImage($inOut);
                }
            }

            // Proses gambar dengan Intervention Image
            $manager = new ImageManager(new Driver());
            $image = $manager->read($response->body());

            // Optional: Resize gambar jika diperlukan
            // $image->resize(800, null, function ($constraint) {
            //     $constraint->aspectRatio();
            //     $constraint->upsize();
            // });

            // Disable temporary
            // Optional: Tambahkan watermark/timestamp
            // $this->addAbsensiWatermark($image, $inOut, $filename);

            // Return sebagai image response
            return response(
                $image->toJpeg(85),
                200,
                [
                    'Content-Type' => 'image/jpeg',
                    'Cache-Control' => 'no-cache, no-store, must-revalidate',
                    'Pragma' => 'no-cache',
                    'Expires' => '0',
                    'Content-Disposition' => 'inline; filename="' . $filename . '"'
                ]
            );
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
            // Untuk error HTTP yang sudah di-abort()
            throw $e;
        } catch (\Throwable $e) {
            \Log::error('Error absensiFotoIn: ' . $e->getMessage(), [
                'inOut' => $inOut,
                'sum_id' => $sum_id,
                'url' => $url ?? null
            ]);

            // Fallback: Tampilkan gambar placeholder jika ada
            return $this->showPlaceholderImage($inOut);
        }
    }

    /**
     * Tambahkan watermark ke gambar absensi
     */
private function addAbsensiWatermark($image, string $inOut, string $filename): void
{
    try {
        $basename = pathinfo($filename, PATHINFO_FILENAME);
        $parts = explode('_', $basename);

        if (count($parts) === 3) {
            [$nik, $date_str, $time_str] = $parts;

            if (strlen($date_str) !== 8 || strlen($time_str) !== 6) {
                return;
            }

            $formatted_date = substr($date_str, 0, 4) . '-' .
                substr($date_str, 4, 2) . '-' .
                substr($date_str, 6, 2);

            $formatted_time = substr($time_str, 0, 2) . ':' .
                substr($time_str, 2, 2) . ':' .
                substr($time_str, 4, 2);

            $text_absensi = strtoupper($inOut) . " - " . $formatted_date . " " . $formatted_time;

            /*
            |--------------------------------------------------------------------------
            | Hitung ukuran box background
            |--------------------------------------------------------------------------
            */
            $fontSize  = 20;
            $paddingX  = 20;
            $paddingY  = 12;
            $fontPath = public_path('fonts/Roboto-Bold.ttf');

            // Hitung dimensi teks
            if (file_exists($fontPath)) {
                $bbox = imagettfbbox($fontSize, 0, $fontPath, $text_absensi);
                $textWidth = $bbox[2] - $bbox[0];
                $textHeight = $bbox[1] - $bbox[7];
            } else {
                $textWidth = mb_strlen($text_absensi) * ($fontSize * 0.6);
                $textHeight = $fontSize;
            }

            $boxWidth  = $textWidth + ($paddingX * 2);
            $boxHeight = $textHeight + ($paddingY * 2);

            $boxX = 10;
            $boxY = 10;

            /*
            |--------------------------------------------------------------------------
            | Background rectangle untuk Intervention Image v3
            |--------------------------------------------------------------------------
            */
            // Buat gambar baru untuk rectangle dengan GD driver
            $manager = \Intervention\Image\ImageManager::gd();
            
            // Atau jika menggunakan parameter array (cara yang salah):
            // $manager = new \Intervention\Image\ImageManager('gd');
            
            $overlay = $manager->create($boxWidth, $boxHeight);
            
            // Isi dengan background transparan
            $overlay->fill('rgba(0,0,0,0)');
            
            // Gambar rectangle dengan background semi-transparan
            $overlay->drawRectangle($boxX, $boxY, function ($draw) use ($boxWidth, $boxHeight) {
                $draw->size($boxWidth, $boxHeight);
                $draw->background('rgba(0,0,0,0.65)');
            });

            // Gabungkan overlay ke image utama
            $image->place($overlay);

            /*
            |--------------------------------------------------------------------------
            | Text watermark
            |--------------------------------------------------------------------------
            */
            $textX = $boxX + $paddingX;
            $textY = $boxY + $paddingY + ($textHeight * 0.2);

            $image->text($text_absensi, $textX, $textY, function ($font) use ($fontPath, $fontSize) {
                if (file_exists($fontPath)) {
                    $font->file($fontPath);
                }
                $font->size($fontSize);
                $font->color('ffffff');
                $font->align('left');
                $font->valign('top');
            });
        }

        /*
        |--------------------------------------------------------------------------
        | Watermark kanan bawah
        |--------------------------------------------------------------------------
        */
        $image->text('ABSENSI', $image->width() - 20, $image->height() - 20, function ($font) {
            $font->size(24);
            $font->color('rgba(255,255,255,0.6)');
            $font->align('right');
            $font->valign('bottom');
        });
    } catch (\Throwable $e) {
        \Log::warning('Failed to add watermark: ' . $e->getMessage());
    }
}


    /**
     * Tampilkan placeholder image jika gambar asli tidak ditemukan
     */
    private function showPlaceholderImage(string $inOut)
    {
        try {
            // Buat gambar placeholder dengan Intervention Image
            $manager = new ImageManager(new Driver());

            // Buat gambar kosong 400x300
            $image = $manager->create(400, 300);

            // Isi dengan background
            $image->fill($inOut === 'in' ? '#2d3748' : '#4a5568');

            // Tambahkan text
            $text = "ABSENSI " . strtoupper($inOut) . "\nTIDAK TERSEDIA";
            $image->text($text, 200, 150, function ($font) {
                $font->file(public_path('fonts/Roboto-Regular.ttf'));
                $font->size(24);
                $font->color('#e2e8f0');
                $font->align('center');
                $font->valign('middle');
            });

            return response(
                $image->toJpeg(80),
                200, // HTTP 404 but still return an image
                ['Content-Type' => 'image/jpeg']
            );
        } catch (\Throwable $e) {
            // Fallback ke response JSON jika gagal buat placeholder
            return response()->json([
                'success' => false,
                'message' => 'Gambar tidak ditemukan'
            ], 200);
        }
    }
}
