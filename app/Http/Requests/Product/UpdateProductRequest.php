<?php

namespace App\Http\Requests\Product;

use App\Http\Requests\BaseApiFormRequest;
use Illuminate\Validation\Rule;
// use Illuminate\Foundation\Http\FormRequest;

class UpdateProductRequest extends BaseApiFormRequest
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
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('products', 'name')
                    ->where('outlet_id', $this->outlet_id)
                    ->ignore($this->route('product'))
            ],
            'description' => 'sometimes|nullable|string',
            'categories' => 'sometimes|array|min:1',
            'categories.*' => 'string|exists:categories,id',
            'is_active' => 'sometimes|boolean',
            'is_variant' => 'sometimes|boolean',
        ];
    }
}
