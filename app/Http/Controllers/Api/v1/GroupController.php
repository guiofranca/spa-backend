<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\v1\Group\CreateGroupRequest;
use App\Http\Requests\Api\v1\Group\DeleteGroupRequest;
use App\Http\Requests\Api\v1\Group\ShowGroupRequest;
use App\Http\Requests\Api\v1\Group\UpdateGroupRequest;
use App\Models\Group;

class GroupController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return Group::query()
            ->join('user_group', 'user_group.group_id', 'groups.id')
            ->where('user_group.user_id', auth()->id())
            ->select('groups.*')
            ->with(['users' => function($q){
                $q->select(['name', 'id']);
            }])
            ->get();
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

        $group->users()->attach([auth()->id()]);
        $group->load(['users' => function($q){
            $q->select(['name', 'id']);
        }]);
        
        return response()->json([
            'message' => 'Group sucessfully created', 
            'group' => $group
        ], 201);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Group  $group
     * @return \Illuminate\Http\Response
     */
    public function show(ShowGroupRequest $request, Group $group)
    {
        $group->load(['users' => function($q){
            $q->select(['name', 'id']);
        }]);
        return response()->json($group);
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
        $group->load(['users' => function($q){
            $q->select(['name', 'id']);
        }]);

        return response()->json([
            'message' => 'Group sucessfully updated',
            'group' => $group,
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Group  $group
     * @return \Illuminate\Http\Response
     */
    public function destroy(DeleteGroupRequest $request, Group $group)
    {
        $group->delete();

        return response()->json([
            'message' => 'Group deleted successfully',
        ], 200);
    }
}
