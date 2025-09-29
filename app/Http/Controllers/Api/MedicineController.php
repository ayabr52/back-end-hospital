<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Medicine; // استيراد موديل Medicine
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Auth; // لاستخدام المستخدم المصادق عليه

class MedicineController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        // يمكن للمدير، المحاسب، موظف الاستقبال، والصيدلي رؤية جميع الأدوية
        $this->authorize('viewAny', Medicine::class);

        $medicines = Medicine::all();
        return response()->json([
            'message' => 'تم جلب الأدوية بنجاح.',
            'medicines' => $medicines,
            'status' => 'success'
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        // يمكن للمدير والصيدلي فقط إضافة أدوية جديدة
        $this->authorize('create', Medicine::class);

        try {
            $request->validate([
                'name' => ['required', 'string', 'max:255', 'unique:medicines'],
                'generic_name' => ['nullable', 'string', 'max:255'],
                'manufacturer' => ['nullable', 'string', 'max:255'],
                'dosage_form' => ['nullable', 'string', 'max:255'],
                'strength' => ['nullable', 'string', 'max:255'],
                'stock_quantity' => ['required', 'integer', 'min:0'],
                'price' => ['required', 'numeric', 'min:0'],
                'expiry_date' => ['nullable', 'date', 'after_or_equal:today'],
                'description' => ['nullable', 'string'],
            ]);

            $medicine = Medicine::create($request->all());

            return response()->json([
                'message' => 'تم إنشاء الدواء بنجاح.',
                'medicine' => $medicine,
                'status' => 'success'
            ], 201);

        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'خطأ في التحقق من صحة البيانات.',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'حدث خطأ غير متوقع: ' . $e->getMessage(),
                'status' => 'error'
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Medicine  $medicine
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Medicine $medicine)
    {
        // يمكن للمدير، المحاسب، موظف الاستقبال، والصيدلي رؤية تفاصيل الدواء
        $this->authorize('view', $medicine);

        return response()->json([
            'message' => 'تم جلب الدواء بنجاح.',
            'medicine' => $medicine,
            'status' => 'success'
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Medicine  $medicine
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, Medicine $medicine)
    {
        // يمكن للمدير والصيدلي فقط تحديث الأدوية
        $this->authorize('update', $medicine);

        try {
            $request->validate([
                'name' => ['required', 'string', 'max:255', 'unique:medicines,name,' . $medicine->id],
                'generic_name' => ['nullable', 'string', 'max:255'],
                'manufacturer' => ['nullable', 'string', 'max:255'],
                'dosage_form' => ['nullable', 'string', 'max:255'],
                'strength' => ['nullable', 'string', 'max:255'],
                'stock_quantity' => ['required', 'integer', 'min:0'],
                'price' => ['required', 'numeric', 'min:0'],
                'expiry_date' => ['nullable', 'date', 'after_or_equal:today'],
                'description' => ['nullable', 'string'],
            ]);

            $medicine->update($request->all());

            return response()->json([
                'message' => 'تم تحديث الدواء بنجاح.',
                'medicine' => $medicine,
                'status' => 'success'
            ]);

        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'خطأ في التحقق من صحة البيانات.',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'حدث خطأ غير متوقع: ' . $e->getMessage(),
                'status' => 'error'
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Medicine  $medicine
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Medicine $medicine)
    {
        // يمكن للمدير والصيدلي فقط حذف الأدوية
        $this->authorize('delete', $medicine);

        try {
            $medicine->delete();

            return response()->json([
                'message' => 'تم حذف الدواء بنجاح.',
                'status' => 'success'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'حدث خطأ أثناء حذف الدواء: ' . $e->getMessage(),
                'status' => 'error'
            ], 500);
        }
    }
}
