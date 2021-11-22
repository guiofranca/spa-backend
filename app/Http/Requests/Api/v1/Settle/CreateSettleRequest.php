<?php

namespace App\Http\Requests\Api\v1\Settle;

use Illuminate\Foundation\Http\FormRequest;

class CreateSettleRequest extends FormRequest
{
    protected function prepareForValidation()
    {
        $this->merge([
            'group_id' => $this->user()?->active_group_id,
            'date' => now(),
            'settled' => false,
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
            'name' => ['required', 'max:255'],
            'group_id' => ['required', 'integer'],
            'date' => ['required', 'date'],
            'settled' => ['required', 'boolean'],
        ];
    }
}
