<?php

namespace App\Http\Requests\Category;

use App\Http\Requests\BaseApiFormRequest;
// use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;


class UpdateCategoryRequest extends BaseApiFormRequest
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
        $category = $this->route('category');
        $outletId = $this->outlet_id 
            ? $this->outlet_id 
            : $category->outlet_id;
        
        $this->merge([
            'outlet_id' => $outletId,
            'slug' => Str::slug($this->name),
            'description' => $this->description ?? 
                Str::is($category->description, $this->name) 
                    ? $this->description
                    : $this->name,
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
            'outlet_id' => 'sometimes|string|exists:outlets,id',
            'name' => 'sometimes|string|max:255',
            'slug' => [
                'required',
                'string',
                Rule::unique('categories', 'slug')
                    ->where('outlet_id', $this->outlet_id)
                    ->ignore($this->route('category')),
            ],
            'description' => 'sometimes|string|max:255'
        ];
    }
}
