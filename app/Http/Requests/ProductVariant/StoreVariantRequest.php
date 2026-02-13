<?php

namespace App\Http\Requests\ProductVariant;

use App\Http\Requests\BaseApiFormRequest;
use Illuminate\Validation\Rule;
// use Illuminate\Foundation\Http\FormRequest;

class StoreVariantRequest extends BaseApiFormRequest
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
            'sku' => [
                'required',
                'string',
                'max:26',
                Rule::unique('product_variants', 'sku')
                    ->where('product_id', $this->route('product'))
                    ->ignore($this->route('variant'))
            ],
            'variant_name' => 'required|string',
            'description' => 'required|nullable|string',
            'harga_awal' => 'required|integer|min:0',
            'min_stock' => 'sometimes|integer|min:0',
        ];
    }
}
