<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\v1\Bill\CreateBillRequest;
use App\Http\Requests\Api\v1\Bill\UpdateBillRequest;
use App\Models\Bill;
use Illuminate\Http\Request;

class BillController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return Bill::query()
            ->where('group_id', auth()->user()->active_group_id)
            ->whereNull('settle_id')
            ->with('user:id,name', 'category:id,name,icon')
            ->get();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(CreateBillRequest $request)
    {
        $bill = Bill::create($request->validated());

        return response()->json([
            'message' => 'Bill successfully created',
            'bill' => $bill->load(['category:id,name,icon', 'user:id,name'])
        ], 201);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Bill $bill)
    {
        return response()->json(['bill' => $bill]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateBillRequest $request, Bill $bill)
    {
        $bill->update($request->validated());

        return response()->json([
            'message' => 'Bill successfully updated',
            'bill' => $bill->load(['category:id,name,icon', 'user:id,name'])
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Bill $bill)
    {
        $bill->delete();

        return response()->json([
            'message' => 'Bill deleted successfully',
        ], 200);
    }
}
