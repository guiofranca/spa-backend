<?php

namespace App\Http\Requests\Api\v1\User;

use App\Models\Group;
use DB;
use Illuminate\Foundation\Http\FormRequest;

class SetActiveGroupRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return DB::table('user_group')
            ->where('user_id', $this->user()->getKey())
            ->where('group_id', $this->active_group_id)
            ->exists();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'active_group_id' => ['required', 'integer'],
        ];
    }
}
