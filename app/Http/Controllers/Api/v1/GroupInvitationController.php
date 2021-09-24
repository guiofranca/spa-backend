<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\v1\CreateGroupInvitationRequest;
use App\Http\Requests\Api\v1\UpdateGroupInvitationRequest;
use App\Models\GroupInvitation;
use Illuminate\Http\Request;

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
        $groupInvitation = GroupInvitation::create($request->validated());

        return response()->json([
            'message' => 'Invitation Created',
            'url' => config('app.url') . "/invite/{$groupInvitation->token}",
        ], 200);
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
            $groupInvitation->group->users()->syncWithoutDetaching(auth()->id());
        }

        return response()->json([
            'message' => 'Invitation ' . ($request->input('accepted') ? 'accepted' : 'rejected'),
            'accepted' => $request->input('accepted'),
        ], 200);
    }
}
