<?php

namespace App\Http\Requests\ProductVariant;

use App\Http\Requests\BaseApiFormRequest;
// use Illuminate\Foundation\Http\FormRequest;

class IndexVariantRequest extends BaseApiFormRequest
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

            'low_stock' => 'nullable|boolean',
            'out_of_stock' => 'nullable|boolean',
        ];
    }
}
