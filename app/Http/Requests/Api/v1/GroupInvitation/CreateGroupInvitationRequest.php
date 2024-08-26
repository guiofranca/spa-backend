<?php

namespace App\Http\Requests\Api\v1\GroupInvitation;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Group;
use Illuminate\Support\Str;

class CreateGroupInvitationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        $group = Group::findOrFail($this->group_id);
        return $group->owner_id == $this->user()->getKey();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'token' => ['required', 'size:36'],
            'group_id' => ['required', 'exists:groups,id'],
        ];
    }
}
