<?php

namespace App\Http\Controllers\api;
use App\Http\Controllers\Controller;
use App\Models\Inventory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class InventoryController extends Controller
{
    /**
     * Display a listing of the resource.
     * عرض جميع الأصناف في المستودع
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $items = Inventory::all();
        return response()->json($items, 200);
    }

    /**
     * Store a newly created resource in storage.
     * إضافة صنف جديد للمستودع
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        // التحقق من صحة البيانات المرسلة
        $validator = Validator::make($request->all(), [
            'item_name' => 'required|string|max:255',
            'quantity' => 'required|integer|min:0',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $item = Inventory::create($request->all());

        return response()->json([
            'message' => 'تمت إضافة الصنف بنجاح',
            'data' => $item
        ], 201);
    }

    /**
     * Display the specified resource.
     * عرض صنف معين حسب الـ ID
     *
     * @param  \App\Models\Inventory  $inventory
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Inventory $inventory)
    {
        return response()->json($inventory, 200);
    }

    /**
     * Update the specified resource in storage.
     * تعديل صنف موجود
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Inventory  $inventory
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, Inventory $inventory)
    {
        // التحقق من صحة البيانات المرسلة
        $validator = Validator::make($request->all(), [
            'item_name' => 'string|max:255',
            'quantity' => 'integer|min:0',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $inventory->update($request->all());

        return response()->json([
            'message' => 'تم تعديل الصنف بنجاح',
            'data' => $inventory
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     * حذف صنف من المستودع
     *
     * @param  \App\Models\Inventory  $inventory
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Inventory $inventory)
    {
        $inventory->delete();

        return response()->json([
            'message' => 'تم حذف الصنف بنجاح'
        ], 200);
    }
}