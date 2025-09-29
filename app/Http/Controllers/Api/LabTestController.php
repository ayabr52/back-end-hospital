<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LabTest; // استيراد موديل LabTest
use App\Models\Patient; // استيراد موديل Patient
use App\Models\Doctor;  // استيراد موديل Doctor
use App\Models\User;    // استيراد موديل User
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Auth; // لاستخدام المستخدم المصادق عليه

class LabTestController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        // يمكن للمدير، الطبيب، وموظف المختبر رؤية جميع التحاليل
        // المريض يرى تحاليله فقط
        $user = Auth::user();
        $labTests = collect();

        if ($user->role->name === 'admin' || $user->role->name === 'doctor' || $user->role->name === 'lab_technician') {
            $labTests = LabTest::with('patient.user', 'doctor.user', 'performer.role')->get();
        } elseif ($user->role->name === 'patient') {
            $patient = $user->patient;
            if ($patient) {
                $labTests = $patient->labTests()->with('patient.user', 'doctor.user', 'performer.role')->get();
            }
        } else {
            return response()->json([
                'message' => 'ليس لديك الصلاحيات الكافية لعرض التحاليل المخبرية.',
                'status' => 'error'
            ], 403);
        }

        return response()->json([
            'message' => 'تم جلب التحاليل المخبرية بنجاح.',
            'lab_tests' => $labTests,
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
        // يمكن للمدير وموظف المختبر فقط إنشاء تحاليل مخبرية
        $this->authorize('create', LabTest::class);

        try {
            $request->validate([
                'patient_id' => ['required', 'exists:patients,id'],
                'doctor_id' => ['nullable', 'exists:doctors,id'],
                'test_type' => ['required', 'string', 'max:255'],
                'test_date' => ['required', 'date', 'before_or_equal:today'],
                'result_status' => ['nullable', 'string', 'in:pending,completed,reviewed'],
                'results' => ['nullable', 'json'], // يمكن أن يكون JSON
                'notes' => ['nullable', 'string'],
            ]);

            $labTest = LabTest::create([
                'patient_id' => $request->patient_id,
                'doctor_id' => $request->doctor_id,
                'performed_by_user_id' => Auth::id(), // المستخدم المصادق عليه هو من يدخل النتائج
                'test_type' => $request->test_type,
                'test_date' => $request->test_date,
                'result_status' => $request->result_status ?? 'pending',
                'results' => $request->results,
                'notes' => $request->notes,
            ]);

            return response()->json([
                'message' => 'تم إنشاء التحليل المخبري بنجاح.',
                'lab_test' => $labTest->load('patient.user', 'doctor.user', 'performer.role'),
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
     * @param  \App\Models\LabTest  $labTest
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(LabTest $labTest)
    {
        // يمكن للمدير، الطبيب المرتبط، موظف المختبر المرتبط، أو المريض المرتبط رؤية تفاصيل التحليل
        $this->authorize('view', $labTest);

        return response()->json([
            'message' => 'تم جلب التحليل المخبري بنجاح.',
            'lab_test' => $labTest->load('patient.user', 'doctor.user', 'performer.role'),
            'status' => 'success'
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\LabTest  $labTest
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, LabTest $labTest)
    {
        // يمكن للمدير وموظف المختبر المرتبط تحديث التحاليل
        $this->authorize('update', $labTest);

        try {
            $request->validate([
                'patient_id' => ['required', 'exists:patients,id'],
                'doctor_id' => ['nullable', 'exists:doctors,id'],
                'test_type' => ['required', 'string', 'max:255'],
                'test_date' => ['required', 'date', 'before_or_equal:today'],
                'result_status' => ['required', 'string', 'in:pending,completed,reviewed'],
                'results' => ['nullable', 'json'],
                'notes' => ['nullable', 'string'],
            ]);

            $labTest->update($request->all());

            return response()->json([
                'message' => 'تم تحديث التحليل المخبري بنجاح.',
                'lab_test' => $labTest->load('patient.user', 'doctor.user', 'performer.role'),
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
     * @param  \App\Models\LabTest  $labTest
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(LabTest $labTest)
    {
        // يمكن للمدير فقط حذف التحاليل المخبرية
        $this->authorize('delete', $labTest);

        try {
            $labTest->delete();

            return response()->json([
                'message' => 'تم حذف التحليل المخبري بنجاح.',
                'status' => 'success'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'حدث خطأ أثناء حذف التحليل المخبري: ' . $e->getMessage(),
                'status' => 'error'
            ], 500);
        }
    }
}
