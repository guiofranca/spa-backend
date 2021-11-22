<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\v1\Settle\CreateSettleRequest;
use App\Http\Requests\Api\v1\Settle\UpdateSettleRequest;
use App\Http\Resources\Api\v1\SettleResource;
use App\Models\Settle;

class SettleController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Settle::class, 'settle');    
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
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(CreateSettleRequest $request)
    {
        $settle = Settle::Create($request->validated());

        $settle->group->unsettledBills()->update(['settle_id' => $settle->id]);

        return (new SettleResource($settle))
            ->additional([
                'message' => 'Settle sucessfully created',
            ])
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Settle  $settle
     * @return \Illuminate\Http\Response
     */
    public function show(Settle $settle)
    {
        return new SettleResource($settle);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Settle  $settle
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateSettleRequest $request, Settle $settle)
    {
        $settle->update($request->validated());

        return (new SettleResource($settle))
            ->additional([
                'message' => 'Settle sucessfully updated',
            ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Settle  $settle
     * @return \Illuminate\Http\Response
     */
    public function destroy(Settle $settle)
    {
        $settle->delete();

        return response()->json([
            'message' => 'Settle successfully deleted'
        ]);
    }
}
