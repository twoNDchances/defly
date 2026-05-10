<?php

namespace App\Http\Requests;

use App\Enums\Wordlist\Type;
use App\Models\Wordlist;
use App\Traits\Requests\Authorization;
use App\Traits\Requests\Error;
use App\Traits\Validators\WordlistValidator;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class WordlistRequest extends FormRequest
{
    use Authorization, Error, WordlistValidator;

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->authorizeResourceRequest(Wordlist::class, 'wordlist');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $wordlist = $this->route('wordlist');
        $ignore = $wordlist instanceof Wordlist ? $wordlist->getKey() : null;

        $rules = match (true) {
            $this->isMethod('get') => $this->paginationRules(),
            $this->isMethod('post'),
            $this->isMethod('put'),
            $this->isMethod('patch') => self::validateWordlist($ignore),
            default => [],
        };

        if ($rules !== [] && ! $this->isMethod('get')) {
            $rules['word_file'] = $this->wordFileRules();
        }

        return $rules;
    }

    public function validationData(): array
    {
        $data = parent::validationData();
        $wordlist = $this->route('wordlist');

        if (! $this->isMethod('patch') || ! $wordlist instanceof Wordlist) {
            return $data;
        }

        return array_replace($this->modelData($wordlist, [
            'name',
            'type',
            'word_file',
            'word_json',
            'description',
        ]), $data);
    }

    private function wordFileRules(): array
    {
        $rules = [
            'required_if:type,'.Type::File->value,
            'nullable',
            function (string $attribute, mixed $value, $fail): void {
                if ($this->input('type') !== Type::File->value) {
                    return;
                }

                $wordlist = $this->route('wordlist');
                $keepsExistingFile = $this->isMethod('patch')
                    && $wordlist instanceof Wordlist
                    && filled($wordlist->word_file)
                    && ! $this->has('word_file')
                    && ! $this->hasFile('word_file');

                if ($keepsExistingFile) {
                    return;
                }

                if (! $this->hasFile('word_file')) {
                    $fail('The '.$attribute.' must be an uploaded file when type is file.');
                }
            },
        ];

        if ($this->hasFile('word_file')) {
            $rules[] = 'file';
            $rules[] = 'mimes:txt';
        }

        return $rules;
    }
}
