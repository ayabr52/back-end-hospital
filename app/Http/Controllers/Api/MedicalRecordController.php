<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MedicalRecord; // استيراد موديل MedicalRecord
use App\Models\Patient;       // استيراد موديل Patient
use App\Models\Doctor;        // استيراد موديل Doctor
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Auth; // لاستخدام المستخدم المصادق عليه

class MedicalRecordController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        // يمكن للمدير والطبيب رؤية جميع السجلات الطبية
        // المريض يرى سجلاته فقط
        $user = Auth::user();
        $medicalRecords = collect();

        if ($user->role->name === 'admin' || $user->role->name === 'doctor') {
            $medicalRecords = MedicalRecord::with('patient.user', 'doctor.user')->get();
        } elseif ($user->role->name === 'patient') {
            $patient = $user->patient;
            if ($patient) {
                $medicalRecords = $patient->medicalRecords()->with('patient.user', 'doctor.user')->get();
            }
        } else {
            return response()->json([
                'message' => 'ليس لديك الصلاحيات الكافية لعرض السجلات الطبية.',
                'status' => 'error'
            ], 403);
        }

        return response()->json([
            'message' => 'تم جلب السجلات الطبية بنجاح.',
            'medical_records' => $medicalRecords,
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
        // يمكن للمدير والطبيب فقط إنشاء سجلات طبية
        $this->authorize('create', MedicalRecord::class);

        try {
            $request->validate([
                'patient_id' => ['required', 'exists:patients,id'],
                'record_date' => ['required', 'date', 'before_or_equal:today'],
                'diagnosis' => ['nullable', 'string', 'max:255'],
                'treatment' => ['nullable', 'string'],
                'notes' => ['nullable', 'string'],
            ]);

            $medicalRecord = MedicalRecord::create([
                'patient_id' => $request->patient_id,
                'doctor_id' => Auth::user()->role->name === 'doctor' ? Auth::user()->doctor->id : null, // إذا كان طبيباً، سجل معرفه
                'record_date' => $request->record_date,
                'diagnosis' => $request->diagnosis,
                'treatment' => $request->treatment,
                'notes' => $request->notes,
            ]);

            return response()->json([
                'message' => 'تم إنشاء السجل الطبي بنجاح.',
                'medical_record' => $medicalRecord->load('patient.user', 'doctor.user'),
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
     * @param  \App\Models\MedicalRecord  $medicalRecord
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(MedicalRecord $medicalRecord)
    {
        // يمكن للمدير، الطبيب المرتبط، أو المريض المرتبط رؤية تفاصيل السجل
        $this->authorize('view', $medicalRecord);

        return response()->json([
            'message' => 'تم جلب السجل الطبي بنجاح.',
            'medical_record' => $medicalRecord->load('patient.user', 'doctor.user'),
            'status' => 'success'
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\MedicalRecord  $medicalRecord
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, MedicalRecord $medicalRecord)
    {
        // يمكن للمدير والطبيب المرتبط فقط تحديث السجلات الطبية
        $this->authorize('update', $medicalRecord);

        try {
            $request->validate([
                'patient_id' => ['required', 'exists:patients,id'],
                'doctor_id' => ['nullable', 'exists:doctors,id'], // يمكن تحديث الطبيب إذا كان المدير
                'record_date' => ['required', 'date', 'before_or_equal:today'],
                'diagnosis' => ['nullable', 'string', 'max:255'],
                'treatment' => ['nullable', 'string'],
                'notes' => ['nullable', 'string'],
            ]);

            $medicalRecord->update($request->all());

            return response()->json([
                'message' => 'تم تحديث السجل الطبي بنجاح.',
                'medical_record' => $medicalRecord->load('patient.user', 'doctor.user'),
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
     * @param  \App\Models\MedicalRecord  $medicalRecord
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(MedicalRecord $medicalRecord)
    {
        // يمكن للمدير فقط حذف السجلات الطبية
        $this->authorize('delete', $medicalRecord);

        try {
            $medicalRecord->delete();

            return response()->json([
                'message' => 'تم حذف السجل الطبي بنجاح.',
                'status' => 'success'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'حدث خطأ أثناء حذف السجل الطبي: ' . $e->getMessage(),
                'status' => 'error'
            ], 500);
        }
    }
}