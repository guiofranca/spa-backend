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
        return $this->bill->isOwner($this->user());
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'description' => ['required', 'min:3', 'max:255'],
            'value' => ['required', 'numeric'],
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'paid_at' => ['nullable', 'date'],
            'due_at' => ['nullable', 'date'],
        ];
    }
}
