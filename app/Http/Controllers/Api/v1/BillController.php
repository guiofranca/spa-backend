<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Requests\Api\v1\Bill\CreateBillRequest;
use App\Http\Requests\Api\v1\Bill\UpdateBillRequest;
use App\Http\Resources\Api\v1\BillResource;
use App\Http\Resources\Api\v1\BillResourceCollection;
use App\Models\Bill;
use Illuminate\Support\Facades\Auth;

/**
 * @tags v1 Despesas
 */
class BillController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Bill::class, 'bill');
    }
    /**
     * Listagem das despesas ativas.
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
     * Criar uma nova despesa.
     *
     * @param  CreateBillRequest  $request
     * @return BillResource
     */
    public function store(CreateBillRequest $request)
    {
        if (Auth::user()->active_group_id == null) {
            return response()->json([
                'message' => __('You must select an active group'),
            ], 422);
        }

        $validated = $request->validated();
        $validated['group_id'] = Auth::user()->active_group_id;
        $validated['user_id'] = Auth::id();

        $bill = Bill::create($validated);

        $bill->load(['category', 'user']);

        return (new BillResource($bill))
            ->additional([
                'message' => __('Bill sucessfully created'),
            ])
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Mostrar uma despesa.
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
     * Atualizar uma despesa.
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
                'message' => __('Bill sucessfully updated'),
            ]);
    }

    /**
     * Apagar uma despesa.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Bill $bill)
    {
        $bill->delete();

        return response()->json([
            'message' => __('Bill deleted successfully'),
        ], 200);
    }
}
