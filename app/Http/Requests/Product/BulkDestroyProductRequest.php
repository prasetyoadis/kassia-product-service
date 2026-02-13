<?php

namespace App\Http\Requests\Product;

use App\Http\Requests\BaseApiFormRequest;
use Illuminate\Foundation\Http\FormRequest;

class BulkDestroyProductRequest extends BaseApiFormRequest
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
            'product_ids'   => 'required|array|min:1',
            'product_ids.*' => 'string|exists:products,id',
        ];
    }
}
