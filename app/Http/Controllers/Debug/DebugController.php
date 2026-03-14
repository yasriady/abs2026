<?php

namespace App\Http\Controllers\Debug;

use App\Http\Controllers\Controller;
use App\Services\JadwalResolverService;
use Illuminate\Http\Request;

class DebugController extends Controller
{
    public function coba1(Request $request)
    {
        $nik = '1471030709820001';
        $date = '2026-02-20';
        $jrs = new JadwalResolverService();
        $j = $jrs->resolve($nik, $date);
        return $j;
    }
}
