<?php

namespace App\Http\Controllers;

use App\Enums\Datatype;
use App\Enums\Engine\Hash;
use App\Enums\Engine\Type as EngineType;
use App\Http\Requests\EngineRelationRequest;
use App\Http\Requests\EngineRequest;
use App\Models\Engine;
use App\Services\ApiPayload;
use App\Traits\Filament\Specifics\Engine\EngineData;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response as HttpResponse;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class EngineController extends Controller
{
    use EngineData;

    public function index(EngineRequest $request): JsonResponse
    {
        $engines = Engine::query()
            ->latest()
            ->paginate($this->perPage($request));

        return response()->json($engines);
    }

    public function store(EngineRequest $request): JsonResponse
    {
        $engine = Engine::query()->create($this->engineData($request));

        return response()->json($engine, SymfonyResponse::HTTP_CREATED);
    }

    public function payload(): JsonResponse
    {
        return response()->json(ApiPayload::resource('engines', [
            'store_index_of' => [
                'method' => 'POST',
                'body' => $this->payloadBody('array-index-of', Datatype::Array->value, EngineType::IndexOf->value, Datatype::String->value, [
                    'position' => 0,
                ]),
            ],
            'store_merge' => [
                'method' => 'POST',
                'body' => $this->payloadBody('array-merge', Datatype::Array->value, EngineType::Merge->value, Datatype::String->value, [
                    'separator' => ',',
                ]),
            ],
            'store_addition' => [
                'method' => 'POST',
                'body' => $this->payloadBody('number-addition', Datatype::Number->value, EngineType::Addition->value, Datatype::Number->value, [
                    'digit' => 10,
                ]),
            ],
            'store_subtraction' => [
                'method' => 'POST',
                'body' => $this->payloadBody('number-subtraction', Datatype::Number->value, EngineType::Subtraction->value, Datatype::Number->value, [
                    'digit' => 5,
                ]),
            ],
            'store_multiplication' => [
                'method' => 'POST',
                'body' => $this->payloadBody('number-multiplication', Datatype::Number->value, EngineType::Multiplication->value, Datatype::Number->value, [
                    'digit' => 2,
                ]),
            ],
            'store_division' => [
                'method' => 'POST',
                'body' => $this->payloadBody('number-division', Datatype::Number->value, EngineType::Division->value, Datatype::Number->value, [
                    'digit' => 2,
                ]),
            ],
            'store_power_of' => [
                'method' => 'POST',
                'body' => $this->payloadBody('number-power-of', Datatype::Number->value, EngineType::PowerOf->value, Datatype::Number->value, [
                    'digit' => 2,
                ]),
            ],
            'store_remainder' => [
                'method' => 'POST',
                'body' => $this->payloadBody('number-remainder', Datatype::Number->value, EngineType::Remainder->value, Datatype::Number->value, [
                    'digit' => 2,
                ]),
            ],
            'store_to_string' => [
                'method' => 'POST',
                'body' => $this->payloadBody('number-to-string', Datatype::Number->value, EngineType::ToString->value, Datatype::String->value),
            ],
            'store_lower' => [
                'method' => 'POST',
                'body' => $this->payloadBody('string-lower', Datatype::String->value, EngineType::Lower->value, Datatype::String->value),
            ],
            'store_upper' => [
                'method' => 'POST',
                'body' => $this->payloadBody('string-upper', Datatype::String->value, EngineType::Upper->value, Datatype::String->value),
            ],
            'store_capitalize' => [
                'method' => 'POST',
                'body' => $this->payloadBody('string-capitalize', Datatype::String->value, EngineType::Capitalize->value, Datatype::String->value),
            ],
            'store_trim' => [
                'method' => 'POST',
                'body' => $this->payloadBody('string-trim', Datatype::String->value, EngineType::Trim->value, Datatype::String->value),
            ],
            'store_trim_left' => [
                'method' => 'POST',
                'body' => $this->payloadBody('string-trim-left', Datatype::String->value, EngineType::TrimLeft->value, Datatype::String->value),
            ],
            'store_trim_right' => [
                'method' => 'POST',
                'body' => $this->payloadBody('string-trim-right', Datatype::String->value, EngineType::TrimRight->value, Datatype::String->value),
            ],
            'store_remove_whitespace' => [
                'method' => 'POST',
                'body' => $this->payloadBody('string-remove-whitespace', Datatype::String->value, EngineType::RemoveWhitespace->value, Datatype::String->value),
            ],
            'store_length' => [
                'method' => 'POST',
                'body' => $this->payloadBody('string-length', Datatype::String->value, EngineType::Length->value, Datatype::Number->value),
            ],
            'store_hash' => [
                'method' => 'POST',
                'body' => $this->payloadBody('string-hash', Datatype::String->value, EngineType::Hash->value, Datatype::String->value, [
                    'hash_method' => Hash::Sha256->value,
                ]),
            ],
            'store_split' => [
                'method' => 'POST',
                'body' => $this->payloadBody('string-split', Datatype::String->value, EngineType::Split->value, Datatype::Array->value, [
                    'separator' => ',',
                ]),
            ],
            'update' => [
                'method' => 'PATCH',
                'path' => '{engine}',
                'body' => [
                    'description' => 'Updated engine description.',
                ],
            ],
            'list_labels' => [
                'method' => 'GET',
                'path' => '{engine}/labels',
            ],
            'attach_labels' => [
                'method' => 'POST',
                'path' => '{engine}/labels',
                'body' => [
                    'ids' => [
                        '<label-id-1>',
                        '<label-id-2>',
                    ],
                ],
            ],
            'detach_labels' => [
                'method' => 'DELETE',
                'path' => '{engine}/labels',
                'body' => [
                    'ids' => [
                        '<label-id-1>',
                    ],
                ],
            ],
        ]));
    }

    public function show(EngineRequest $request, Engine $engine): JsonResponse
    {
        return response()->json($engine);
    }

    public function update(EngineRequest $request, Engine $engine): JsonResponse
    {
        $engine->update($this->engineData($request));

        return response()->json($engine->refresh());
    }

    public function destroy(EngineRequest $request, Engine $engine): HttpResponse
    {
        $engine->delete();

        return response()->noContent();
    }

    public function labels(EngineRelationRequest $request, Engine $engine): JsonResponse
    {
        return response()->json($engine->labels()
            ->latest()
            ->get());
    }

    public function attachLabels(EngineRelationRequest $request, Engine $engine): JsonResponse
    {
        $engine->labels()->syncWithoutDetaching($request->validated('ids', []));

        return response()->json($engine->labels()
            ->latest()
            ->get());
    }

    public function detachLabels(EngineRelationRequest $request, Engine $engine): JsonResponse
    {
        $engine->labels()->detach($request->validated('ids', []));

        return response()->json($engine->labels()
            ->latest()
            ->get());
    }

    private function engineData(EngineRequest $request): array
    {
        $data = self::saveForm($request->validated());

        return $this->onlyFields($data, [
            'name',
            'input_datatype',
            'type',
            'configurations',
            'output_datatype',
            'description',
        ]);
    }

    private function payloadBody(
        string $name,
        string $inputDatatype,
        string $type,
        string $outputDatatype,
        array $extra = []
    ): array {
        return [
            'name' => $name,
            'input_datatype' => $inputDatatype,
            'type' => $type,
            'output_datatype' => $outputDatatype,
            'description' => 'Engine API example.',
            ...$extra,
        ];
    }
}
