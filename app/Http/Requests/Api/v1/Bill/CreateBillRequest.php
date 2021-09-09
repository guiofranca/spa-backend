<?php

namespace App\Http\Requests\Api\v1\Bill;

use Illuminate\Foundation\Http\FormRequest;

class CreateBillRequest extends FormRequest
{
    protected function prepareForValidation()
    {
        $this->merge([
            'user_id' => $this->user()?->getKey(),
            'group_id' => $this->user()?->active_group_id,
        ]);
    }

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
            'description' => ['required', 'min:3', 'max:255'],
            'value' => ['required', 'numeric'],
            'user_id' => ['required'],
            'group_id' => ['required'],
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'paid_at' => ['required', 'date'],
            'due_at' => ['nullable', 'date'],
        ];
    }

    public function messages(){
        return [
            'group_id.required' => 'You must select an active group'
        ];
    }
}
