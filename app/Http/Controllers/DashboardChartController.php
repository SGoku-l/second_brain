<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\BuildsUsageCharts;
use App\Models\TokenUsage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardChartController extends Controller
{
    use BuildsUsageCharts;

    public function tokens(Request $request): JsonResponse
    {
        return response()->json($this->dailySeries(
            TokenUsage::query()->where('user_id', $request->user()->id),
            'recorded_at',
            'sum',
        ));
    }
}
