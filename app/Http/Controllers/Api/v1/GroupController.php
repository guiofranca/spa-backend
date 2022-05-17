<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\v1\Group\CreateGroupRequest;
use App\Http\Requests\Api\v1\Group\DeleteGroupRequest;
use App\Http\Requests\Api\v1\Group\ShowGroupRequest;
use App\Http\Requests\Api\v1\Group\UpdateGroupRequest;
use App\Http\Resources\Api\v1\GroupResource;
use App\Http\Resources\Api\v1\GroupResourceCollection;
use App\Models\Group;
use App\Models\GroupMember;
use Illuminate\Http\Request;

class GroupController extends Controller
{

    public function __construct()
    {
        $this->authorizeResource(Group::class, 'group');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return new GroupResourceCollection(Group::query()
            ->join('group_members as gm', 'gm.group_id', 'groups.id')
            ->where('gm.user_id', auth()->id())
            ->select('groups.*')
            ->with(['groupMembers.user'])
            ->get());
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(CreateGroupRequest $request)
    {
        $group = Group::create([
            'name' => $request->name,
            'description' => $request->description,
            'owner_id' => auth()->id()
        ]);

        GroupMember::create([
            'user_id' => auth()->id(),
            'group_id' => $group->id,
            'contribution' => 100,
        ]);
        
        return (new GroupResource($group))
            ->additional([
                'message' => __('Group sucessfully created'),
            ])
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Group  $group
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, Group $group)
    {
        return new GroupResource($group->load('groupMembers.user'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Group  $group
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateGroupRequest $request, Group $group)
    {
        $group->update($request->validated());
        $group->load('groupMembers.user');

        return (new GroupResource($group))
            ->additional([
                'message' => __('Group sucessfully updated'),
            ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Group  $group
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, Group $group)
    {
        $group->delete();

        return response()->json([
            'message' => __('Group deleted successfully'),
        ], 200);
    }
}
