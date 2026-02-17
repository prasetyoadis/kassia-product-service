<?php

namespace App\Http\Requests\Product;

use App\Http\Requests\BaseApiFormRequest;
use Illuminate\Validation\Rule;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Cache;
// use Illuminate\Foundation\Http\FormRequest;
// use Illuminate\Contracts\Validation\Validator;
// use Symfony\Component\HttpFoundation\Response;
// use Illuminate\Http\Exceptions\HttpResponseException;



class StoreProductRequest extends BaseApiFormRequest
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
        $userId = JWTAuth::parseToken()->getPayload()->get('sub');
        $outletId = $this->outlet_id 
            ? $this->outlet_id 
            : Cache::get("active_outlet:user:{$userId}");
        
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
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('products', 'name')
                    ->where('outlet_id', $this->outlet_id)
            ],
            'description' => 'nullable|string',
            'sku' => [
                Rule::requiredIf(fn() => $this->boolean('is_variant') === false),
                'string',
                'max:26',
                Rule::unique('product_variants', 'sku')
                    ->where('outlet_id', $this->outlet_id)
            ],
            'harga_awal' => [
                Rule::requiredIf(fn() => $this->boolean('is_variant') === false),
                'nullable',
                'numeric',
                'min:0'
            ],
            'is_variant' => 'required|boolean',
            
            'categories' => 'required|array|min:1',
            'categories.*' => 'string|exists:categories,id',
            
            // 'image' => 'required|image|mimes:jpg,jpeg,png,webp|max:2048',
            
            // 'variants' => 'required_if:is_variant,true|array',
            // 'variants.*.sku' => 'required_if:is_variant,true|string|max:26|unique:product_variants,sku',
            // 'variants.*.variant_name' => 'required_if:is_variant,true|string|max:255',
            // 'variants.*.description' => 'nullable|string',
            // 'variants.*.harga_awal' => 'required_if:is_variant,true|numeric|min:0'
        ];
    }

    // protected function failedValidation(Validator $validator)
    // {
    //     throw new HttpResponseException(
    //         response()->json([
    //             'statusCode' => Response::HTTP_UNPROCESSABLE_ENTITY,
    //             'statusMessage' => 'Unprocessable Entity',
    //             'statusDescription' => 'Validation failed for the given request',
    //             'result' => [
    //                 'errorCode' => '21',
    //                 'errorMessage' => 'Validation failed',
    //                 'errors' => $validator->errors(),
    //             ],
    //         ], Response::HTTP_UNPROCESSABLE_ENTITY)
    //     );
    // }
}
