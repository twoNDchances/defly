<?php

namespace App\Http\Requests;

use App\Models\Defender;
use App\Models\Report;
use App\Traits\Requests\Authorization;
use App\Traits\Requests\Error;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class ReportRequest extends FormRequest
{
    use Authorization, Error;

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $defender = $this->route('defender');
        $report = $this->route('report');

        if (! $defender instanceof Defender) {
            return false;
        }

        return match (true) {
            $this->isMethod('get') && $report instanceof Report => $this->canAccessReport('view', $defender, $report),
            $this->isMethod('get') => $this->allows('viewAny', Report::class),
            $this->isMethod('delete') && $report instanceof Report => $this->canAccessReport('delete', $defender, $report),
            default => false,
        };
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return match (true) {
            $this->isMethod('get') => $this->paginationRules(),
            default => [],
        };
    }

    private function canAccessReport(string $ability, Defender $defender, Report $report): bool
    {
        if ((string) $report->getRawOriginal('created_by') !== (string) $defender->getKey()) {
            return false;
        }

        return $this->allows($ability, $report);
    }
}
