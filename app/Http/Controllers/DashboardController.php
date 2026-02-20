<?php

namespace App\Http\Controllers;

use App\Services\DashboardService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index(Request $request, DashboardService $dashboardService)
    {
        $user = $request->user();
        $filters = $dashboardService->normalizeFilters($request->all(), $user);

        return Inertia::render('Dashboard', [
            'filters' => $filters,
            'units' => $dashboardService->unitsFor($user),
            'subUnits' => $dashboardService->subUnitsFor($filters['unit_id']),
            ...$dashboardService->build($filters, $user),
        ]);
    }
}
