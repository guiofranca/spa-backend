<?php

namespace App\Http\Requests\Api\v1\GroupInvitation;

use App\Models\GroupInvitation;
use App\Models\GroupMember;
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
        $invitation = GroupInvitation::whereToken($this->group_invitation)->firstOrFail();
        return GroupMember::query()
            ->where('user_id', $this->user_id)
            ->where('group_id', $invitation->group_id)
            ->doesntExist();
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
