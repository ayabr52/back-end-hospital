<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Department; // استيراد موديل Department
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class DepartmentController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $departments = Department::all();
        return response()->json([
            'message' => 'تم جلب الأقسام بنجاح.',
            'departments' => $departments,
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
        try {
            $request->validate([
                'name' => ['required', 'string', 'max:255', 'unique:departments'],
                'description' => ['nullable', 'string'],
                'specialty' => ['nullable', 'string', 'max:255'],
            ]);

            $department = Department::create($request->all());

            return response()->json([
                'message' => 'تم إنشاء القسم بنجاح.',
                'department' => $department,
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
     * @param  \App\Models\Department  $department
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Department $department)
    {
        return response()->json([
            'message' => 'تم جلب القسم بنجاح.',
            'department' => $department,
            'status' => 'success'
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Department  $department
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, Department $department)
    {
        try {
            $request->validate([
                'name' => ['required', 'string', 'max:255', 'unique:departments,name,' . $department->id],
                'description' => ['nullable', 'string'],
                'specialty' => ['nullable', 'string', 'max:255'],
            ]);

            $department->update($request->all());

            return response()->json([
                'message' => 'تم تحديث القسم بنجاح.',
                'department' => $department,
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
     * @param  \App\Models\Department  $department
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Department $department)
    {
        try {
            $department->delete();

            return response()->json([
                'message' => 'تم حذف القسم بنجاح.',
                'status' => 'success'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'حدث خطأ أثناء حذف القسم: ' . $e->getMessage(),
                'status' => 'error'
            ], 500);
        }
    }
}
