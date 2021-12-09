<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\v1\Bill\CreateBillRequest;
use App\Http\Requests\Api\v1\Bill\UpdateBillRequest;
use App\Http\Resources\Api\v1\BillResource;
use App\Http\Resources\Api\v1\BillResourceCollection;
use App\Models\Bill;
use Illuminate\Http\Request;

class BillController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Bill::class, 'bill');    
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return new BillResourceCollection(Bill::query()
            ->where('group_id', auth()->user()->active_group_id)
            ->whereNull('settle_id')
            ->with('user', 'category')
            ->orderBy('id', 'desc')
            ->get());
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

        $bill->load(['category', 'user']);

        return (new BillResource($bill))
            ->additional([
                'message' => 'Bill sucessfully created',
            ])
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Bill $bill)
    {
        $bill->load(['category', 'user']);

        return new BillResource($bill);
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

        $bill->load(['category', 'user']);

        return (new BillResource($bill))
            ->additional([
                'message' => 'Group sucessfully created',
            ]);
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
