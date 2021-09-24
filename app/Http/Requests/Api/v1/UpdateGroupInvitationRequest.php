<?php

namespace App\Http\Requests\Api\v1;

use Illuminate\Foundation\Http\FormRequest;

class UpdateGroupInvitationRequest extends FormRequest
{
    protected function prepareForValidation()
    {
        $this->merge([
            'user_id' => $this->user()->getKey(),
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
            'accepted' => ['required', 'boolean'],
            'user_id' => ['required'],
        ];
    }
}
