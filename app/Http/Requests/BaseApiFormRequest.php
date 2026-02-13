<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use App\Helpers\GeneralResponse;

abstract class BaseApiFormRequest extends FormRequest
{
    /**
     * Handle a failed validation attempt.
     *
     * @param  \Illuminate\Contracts\Validation\Validator  $validator
     * @return void
     */
    protected function failedValidation(Validator $validator): void
    {
        $errorCode = $this->resolveError($validator);

        throw new HttpResponseException(
            GeneralResponse::error(
                statusCode: 422,
                errorCode: $errorCode
            )
        );
    }

    /**
     * Resolve HTTP status + errorCode
     */
    protected function resolveError(Validator $validator): string
    {
        $failed = $validator->failed();

        $hasRequired = false;
        $hasType = false;

        foreach ($failed as $rules) {
            $ruleNames = array_keys($rules);

            # Required errorCode 21 (prioritas paling tinggi)
            if (array_intersect($ruleNames, [
                'Required',
                'RequiredIf',
                'RequiredWith',
                'RequiredWithout',
                'RequiredWithoutAll',
                'RequiredWithAll',
            ])) {
                return '21';
            }

            # Type errorCode 23
            if (array_intersect($ruleNames, [
                'String',
                'Integer',
                'Numeric',
                'Boolean',
                'Array',
            ])) {
                return '23';
            }
            /**
             * Semua rule lain:
             * unique, exists, min, max, in, required_if (value), dll
             */
            return '24';
        }

        # fallback global
        return '20';
    }
}
