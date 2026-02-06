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
        $nik = MasterPegawai::find($id)?->nik;
        return $this->showByNik($nik);
    }

    private function showByNik(string $nik)
    {
        $url = "http://192.10.10.2/penduduk/foto/{$nik}.jpg";

        try {
            $response = Http::timeout(3)->get($url);
            abort_unless($response->successful(), 404);

            $manager = new ImageManager(new Driver());
            $image = $manager->read($response->body());

            // ❗ WAJIB positional argument (PHP 8)
            $image->resize(300, null);

            // watermark
            $image->text(
                'ABSENSI',
                $image->width() - 10,
                $image->height() - 10,
                function ($font) {
                    $font->size(18);
                    $font->color('rgba(255,255,255,0.5)');
                    $font->align('right');
                    $font->valign('bottom');
                }
            );

            return response(
                $image->toJpeg(85),
                200,
                ['Content-Type' => 'image/jpeg']
            );
        } catch (\Throwable $e) {
            abort(404);
        }
    }
}
