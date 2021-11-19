<?php

namespace App\Http\Requests\Api\v1\Bill;

use Illuminate\Foundation\Http\FormRequest;

class UpdateBillRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'description' => ['sometimes', 'required', 'min:3', 'max:255'],
            'value' => ['sometimes', 'required', 'numeric'],
            'category_id' => ['sometimes', 'required', 'integer', 'exists:categories,id'],
            'paid_at' => ['nullable', 'date'],
            'due_at' => ['nullable', 'date'],
        ];
    }
}
