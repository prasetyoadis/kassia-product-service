<?php

namespace App\Http\Requests\ProductVariant;

use App\Http\Requests\BaseApiFormRequest;
// use Illuminate\Foundation\Http\FormRequest;

class UpdateVariantStockRequest extends BaseApiFormRequest
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
            'type'     => 'required|in:in,out',
            'quantity' => 'required|integer|min:1',
            'note'     => 'nullable|string',
        ];
    }
}
