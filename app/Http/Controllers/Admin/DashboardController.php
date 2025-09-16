<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\StatisticsService;
use Illuminate\Http\JsonResponse;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __construct(
        private StatisticsService $statisticsService
    ) {}

    public function index(): Response
    {
        return Inertia::render('Admin/Dashboard');
    }

    public function getFinancialStatistics(): JsonResponse
    {
        return response()->json($this->statisticsService->getFinancialStatistics());
    }

    public function getBusinessPerformanceStatistics(): JsonResponse
    {
        return response()->json($this->statisticsService->getBusinessPerformanceStatistics());
    }

    public function getStocksStatistics(): JsonResponse
    {
        return response()->json($this->statisticsService->getStocksStatistics());
    }

    public function getCustomerAnalytics(): JsonResponse
    {
        return response()->json($this->statisticsService->getCustomerAnalytics());
    }
}
