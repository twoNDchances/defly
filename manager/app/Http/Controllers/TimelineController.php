<?php

namespace App\Http\Controllers;

use App\Http\Requests\TimelineRequest;
use App\Models\Timeline;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response as HttpResponse;

class TimelineController extends Controller
{
    public function index(TimelineRequest $request): JsonResponse
    {
        $timelines = Timeline::query()
            ->latest()
            ->paginate($this->perPage($request));

        return response()->json($timelines);
    }

    public function show(TimelineRequest $request, Timeline $timeline): JsonResponse
    {
        return response()->json($timeline);
    }

    public function destroy(TimelineRequest $request, Timeline $timeline): HttpResponse
    {
        $timeline->delete();

        return response()->noContent();
    }
}
