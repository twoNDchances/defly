<?php

namespace App\Http\Controllers;

use App\Http\Requests\PatternRequest;
use App\Models\Pattern;
use Illuminate\Http\JsonResponse;

class PatternController extends Controller
{
    public function index(PatternRequest $request): JsonResponse
    {
        $patterns = Pattern::query()
            ->latest()
            ->paginate($this->perPage($request));

        return response()->json($patterns);
    }

    public function show(PatternRequest $request, Pattern $pattern): JsonResponse
    {
        return response()->json($pattern);
    }
}
