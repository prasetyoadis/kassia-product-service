<?php

namespace App\Http\Requests\Product;

use App\Helpers\GeneralResponse;
use App\Http\Requests\BaseApiFormRequest;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;

class IndexProductRequest extends BaseApiFormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'search' => 'nullable|string|max:100',

            'category' => 'nullable|string|exists:categories,id',

            'page' => 'nullable|integer|min:1',
            'per_page' => 'nullable|integer|min:7|max:100',

            // 'price_min' => 'nullable|numeric|min:0',
            // 'price_max' => 'nullable|numeric|gte:price_min',

            'sort_by' => 'nullable|in:name,created_at,updated_at',
            'order' => 'nullable|in:asc,desc',

            'low_stock' => 'nullable|boolean',
            'out_of_stock' => 'nullable|boolean',
            'low_stock_threshold' => 'nullable|integer|min:1',
        ];
    }

    /**
     * Enforce business rule:
     * low_stock & out_of_stock tidak boleh barengan
     */
    protected function prepareForValidation(): void
    {
        if ($this->boolean('low_stock') && $this->boolean('out_of_stock')) {
            throw new HttpResponseException(
                GeneralResponse::error(
                    statusCode: 422,
                    errorCode: '24',
                )
            );
        }
    }
}
