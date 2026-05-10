<?php

namespace App\Http\Controllers;

use App\Http\Requests\DefenderRequest;
use App\Models\Defender;
use App\Services\ApiPayload;
use App\Traits\Filament\Specifics\Defender\DefenderData;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response as HttpResponse;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class DefenderController extends Controller
{
    use DefenderData;

    public function index(DefenderRequest $request): JsonResponse
    {
        $defenders = Defender::query()
            ->latest()
            ->paginate($this->perPage($request));

        return response()->json($defenders);
    }

    public function store(DefenderRequest $request): JsonResponse
    {
        $defender = Defender::query()->create($this->defenderData($request));

        return response()->json($defender, SymfonyResponse::HTTP_CREATED);
    }

    public function payload(): JsonResponse
    {
        return response()->json(ApiPayload::resource('defenders', [
            'store' => [
                'method' => 'POST',
                'body' => [
                    'name' => 'primary-defender',
                    'proxy_port' => 9948,
                    'environment_variables' => $this->defaultEnvironmentVariables(),
                    'description' => 'Defender API example.',
                ],
            ],
            'update' => [
                'method' => 'PATCH',
                'path' => '{defender}',
                'body' => [
                    'description' => 'Updated defender description.',
                ],
            ],
        ]));
    }

    public function show(DefenderRequest $request, Defender $defender): JsonResponse
    {
        return response()->json($defender);
    }

    public function update(DefenderRequest $request, Defender $defender): JsonResponse
    {
        $defender->update($this->defenderData($request));

        return response()->json($defender->refresh());
    }

    public function destroy(DefenderRequest $request, Defender $defender): HttpResponse
    {
        $defender->delete();

        return response()->noContent();
    }

    private function defenderData(DefenderRequest $request): array
    {
        $data = self::saveForm($request->validated());

        return $this->onlyFields($data, [
            'name',
            'proxy_port',
            'environment_variables',
            'description',
        ]);
    }

    private function defaultEnvironmentVariables(): array
    {
        return [
            ...self::environmentVariablesToMap(self::commonEnvironmentVariables()),
            ...self::environmentVariablesToMap(self::serverEnvironmentVariables()),
            ...self::environmentVariablesToMap(self::proxyEnvironmentVariables()),
        ];
    }
}
