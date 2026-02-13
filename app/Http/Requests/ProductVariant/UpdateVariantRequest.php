<?php

namespace App\Http\Requests\ProductVariant;

use App\Http\Requests\BaseApiFormRequest;
// use Illuminate\Foundation\Http\FormRequest;

class UpdateVariantRequest extends BaseApiFormRequest
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
            'sku' => 'sometimes|string|max:26|unique:product_variants,sku',
            'variant_name' => 'sometimes|string',
            'description' => 'sometimes|nullable|string',
            'harga_awal' => 'sometimes|integer|min:0',
            'min_stock' => 'sometimes|integer|min:0',
            'is_active' => 'sometimes|boolean',
        ];
    }
}
