<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Requests\Api\v1\GroupInvitation\CreateGroupInvitationRequest;
use App\Http\Requests\Api\v1\GroupInvitation\UpdateGroupInvitationRequest;
use App\Models\GroupInvitation;
use App\Models\GroupMember;
use Illuminate\Support\Str;

/**
 * @tags v1 Convite para grupo
 */
class GroupInvitationController extends Controller 
{
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(CreateGroupInvitationRequest $request)
    {
        $validated = $request->validated();
        $validated['token'] = Str::uuid();
        $groupInvitation = GroupInvitation::create($validated);

        return response()->json([
            'message' => __('Invitation Created'),
            'url' => config('app.url') . "/invite/{$groupInvitation->token}",
            'token' => $groupInvitation->token,
        ], 201);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $token
     * @return \Illuminate\Http\Response
     */
    public function show($token)
    {
        $groupInvitation = GroupInvitation::query()
            ->whereToken($token)
            ->whereNull('accepted')
            ->with('group')
            ->firstOrFail();

        return response()->json($groupInvitation);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $token
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateGroupInvitationRequest $request, $token)
    {
        $groupInvitation = GroupInvitation::query()
            ->whereToken($token)
            ->whereNull('accepted')
            ->firstOrFail();

        $groupInvitation->update($request->validated());

        if($request->input('accepted')) {
            GroupMember::create([
                'group_id' => $groupInvitation->group_id,
                'user_id' => auth()->id(),
                'contribution' => 100,
            ]);
        }

        return response()->json([
            'message' => __('Invitation') . ' ' . ($request->input('accepted') ? __('accepted') : __('rejected')),
            'accepted' => $request->input('accepted'),
        ], 200);
    }
}
