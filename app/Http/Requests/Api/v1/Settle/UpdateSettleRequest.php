<?php

namespace App\Http\Requests\Api\v1\Settle;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSettleRequest extends FormRequest
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
            'name' => ['sometimes', 'required', 'max:255'],
            'settled' => ['sometimes', 'required', 'boolean'],
        ];
    }
}
