<?php

namespace App\Http\Controllers;

use App\Http\Requests\ReportRequest;
use App\Models\Defender;
use App\Models\Report;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response as HttpResponse;

class ReportController extends Controller
{
    public function index(ReportRequest $request, Defender $defender): JsonResponse
    {
        $reports = $defender->reports()
            ->latest()
            ->paginate($this->perPage($request));

        return response()->json($reports);
    }

    public function show(ReportRequest $request, Defender $defender, Report $report): JsonResponse
    {
        $report = $this->defenderReport($defender, $report);
        abort_if(! $report, 404);

        return response()->json($report);
    }

    public function destroy(ReportRequest $request, Defender $defender, Report $report): HttpResponse
    {
        $report = $this->defenderReport($defender, $report);
        abort_if(! $report, 404);

        $report->delete();

        return response()->noContent();
    }

    private function defenderReport(Defender $defender, Report $report): ?Report
    {
        return $defender->reports()
            ->whereKey($report->getKey())
            ->first();
    }
}
