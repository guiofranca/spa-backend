<?php

namespace App\Http\Requests\Api\v1\GroupMember;

use Illuminate\Foundation\Http\FormRequest;

class UpdateGroupMemberRequest extends FormRequest
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
            'contribution' => ['required', 'integer', 'between:1,100'],
        ];
    }
}
