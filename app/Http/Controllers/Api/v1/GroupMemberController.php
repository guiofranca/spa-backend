<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\v1\GroupMember\UpdateGroupMemberRequest;
use App\Http\Resources\Api\v1\GroupMemberResource;
use App\Models\GroupMember;
use Illuminate\Http\Request;

class GroupMemberController extends Controller
{

    public function __construct()
    {
        $this->authorizeResource(GroupMember::class, 'group_member');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateGroupMemberRequest $request, GroupMember $groupMember)
    {
        $groupMember->update($request->validated());

        return (new GroupMemberResource($groupMember))
            ->additional([
                'message' => __('Group member sucessfully updated'),
            ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(GroupMember $groupMember)
    {
        $groupMember->delete();

        return response()->json([
            'message' => __('Group Member removed'),
        ]);
    }
}
