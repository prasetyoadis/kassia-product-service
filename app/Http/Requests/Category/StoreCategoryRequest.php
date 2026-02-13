<?php

namespace App\Http\Requests\Category;

use App\Http\Requests\BaseApiFormRequest;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\Rule;
use Tymon\JWTAuth\Facades\JWTAuth;

class StoreCategoryRequest extends BaseApiFormRequest
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
            'slug' => Str::slug($this->name),
            'description' => $this->description ?? $this->name,
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
            'name' => 'required|string|max:255',
            'slug' => [
                'required',
                'string',
                Rule::unique('categories', 'slug')
                    ->where('outlet_id', $this->outlet_id)
                    ->ignore($this->route('category')),
            ],
            'description' => 'required|string|max:255'
        ];
    }
}
