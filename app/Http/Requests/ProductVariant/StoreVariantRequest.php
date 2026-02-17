<?php

namespace App\Http\Requests\ProductVariant;

use App\Http\Requests\BaseApiFormRequest;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\Rule;
use Tymon\JWTAuth\Facades\JWTAuth;
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
     * Persiapkan data untuk divalidasi.
     *
     * @return void
     */
    protected function prepareForValidation(): void
    {
        $product = $this->route('product');
        $userId = JWTAuth::parseToken()->getPayload()->get('sub');
        $outletId = $this->outlet_id 
            ? $this->outlet_id 
            : $product->outlet_id ?? Cache::get("active_outlet:user:{$userId}");
        
        $this->merge([
            'outlet_id' => $outletId,
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'outlet_id' => 'required|string|exists:outlets,id',
            'sku' => [
                'required',
                'string',
                'max:26',
                Rule::unique('product_variants', 'sku')
                    ->where('outlet_id', $this->outlet_id)
            ],
            'variant_name' => 'required|string',
            'description' => 'required|nullable|string',
            'harga_awal' => 'required|integer|min:0',
            'min_stock' => 'sometimes|integer|min:0',
        ];
    }
}
