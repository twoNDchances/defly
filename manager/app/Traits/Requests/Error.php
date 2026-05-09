<?php

namespace App\Traits\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Symfony\Component\HttpFoundation\Response;

trait Error
{
    protected function failedAuthorization(): void
    {
        throw new HttpResponseException(response()->json([
            'message' => __('apis.messages.failed.authorization'),
        ], Response::HTTP_FORBIDDEN));
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(response()->json([
            'message' => __('apis.messages.failed.validation'),
            'errors' => $validator->errors(),
        ], Response::HTTP_UNPROCESSABLE_ENTITY));
    }
}
