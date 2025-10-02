<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Prescription; // استيراد موديل Prescription
use App\Models\Patient;      // استيراد موديل Patient
use App\Models\Doctor;       // استيراد موديل Doctor
use App\Models\Medicine;     // استيراد موديل Medicine
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Auth; // لاستخدام المستخدم المصادق عليه
use Illuminate\Support\Facades\DB; // لاستخدام المعاملات

class PrescriptionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        // يمكن للمدير والطبيب والصيدلي رؤية جميع الوصفات
        // المريض يرى وصفاته فقط
        $user = Auth::user();
        $prescriptions = collect();

        if ($user->role->name === 'admin' || $user->role->name === 'doctor' || $user->role->name === 'pharmacist') {
            $prescriptions = Prescription::with('patient.user', 'doctor.user', 'medicines')->get();
        } elseif ($user->role->name === 'patient') {
            $patient = $user->patient;
            if ($patient) {
                $prescriptions = $patient->prescriptions()->with('patient.user', 'doctor.user', 'medicines')->get();
            }
        } else {
            return response()->json([
                'message' => 'ليس لديك الصلاحيات الكافية لعرض وصفات الأدوية.',
                'status' => 'error'
            ], 403);
        }

        return response()->json([
            'message' => 'تم جلب وصفات الأدوية بنجاح.',
            'prescriptions' => $prescriptions,
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
        // يمكن للمدير والطبيب و الصيدلي إنشاء وصفات أدوية
        $this->authorize('create', Prescription::class);

         try {
$request->validate([
    'patient_id' => ['required', 'exists:patients,id'],
    'prescription_date' => ['required', 'date', 'before_or_equal:today'],
    'notes' => ['nullable', 'string'],
    'doctor_id' => ['nullable', 'exists:doctors,id'],
    'medicines' => ['required', 'array', 'min:1'],
    'medicines.*.medicine_id' => ['required', 'exists:medicines,id'],
    'medicines.*.dosage' => ['nullable', 'string', 'max:255'],
    'medicines.*.frequency' => ['nullable', 'string', 'max:255'],
    'medicines.*.duration' => ['nullable', 'string', 'max:255'],
    'medicines.*.instructions' => ['nullable', 'string'],
]);



            DB::beginTransaction();


        // تحديد الطبيب حسب الدور أو من الطلب
        $user = Auth::user();
        $doctorId = null;

        if ($user->role->name === 'doctor') {
            $doctorId = $user->doctor->id;
        } elseif (in_array($user->role->name, ['admin', 'pharmacist'])) {
            $doctorId = $request->doctor_id; // يجب أن يُمرر يدوياً
        }

            $prescription = Prescription::create([
                'patient_id' => $request->patient_id,
                'doctor_id' => $doctorId, // إذا كان طبيباً، سجل معرفه
                'prescription_date' => $request->prescription_date,
                'notes' => $request->notes,
            ]);
$medicinesToAttach = [];
$user = Auth::user(); // المستخدم الحالي

foreach ($request->medicines as $med) {
    $medicineModel = Medicine::find($med['medicine_id']);

    $dosage = $med['dosage'] ?? ($user->role->name === 'doctor' ? null : $medicineModel->strength);
    $frequency = $med['frequency'] ?? ($user->role->name === 'doctor' ? null : 'مرة يومياً');
    $duration = $med['duration'] ?? ($user->role->name === 'doctor' ? null : '5 أيام');
    $instructions = $med['instructions'] ?? ($user->role->name === 'doctor' ? null : 'بعد الطعام');

    $medicinesToAttach[$med['medicine_id']] = [
        'dosage' => $dosage,
        'frequency' => $frequency,
        'duration' => $duration,
        'instructions' => $instructions,
    ];


}

            $prescription->medicines()->attach($medicinesToAttach);

            DB::commit();

          return response()->json([
    'message' => 'تم إنشاء وصفة الدواء بنجاح.',
    'prescription' => $prescription->load('patient.user', 'doctor.user', 'medicines'),
    'doctor_name' => optional($prescription->doctor->user)->name,
    'status' => 'success'
], 201);


        } catch (ValidationException $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'خطأ في التحقق من صحة البيانات.',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'حدث خطأ غير متوقع: ' . $e->getMessage(),
                'status' => 'error'
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Prescription  $prescription
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Prescription $prescription)
    {
        // يمكن للمدير، الطبيب المرتبط، أو المريض المرتبط رؤية تفاصيل الوصفة
        $this->authorize('view', $prescription);

        return response()->json([
            'message' => 'تم جلب وصفة الدواء بنجاح.',
            'prescription' => $prescription->load('patient.user', 'doctor.user', 'medicines'),
            'status' => 'success'
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Prescription  $prescription
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, Prescription $prescription)
    {
        // يمكن للمدير والطبيب المرتبط فقط تحديث وصفات الأدوية
        $this->authorize('update', $prescription);

        try {
            $request->validate([
                'patient_id' => ['required', 'exists:patients,id'],
                'doctor_id' => ['nullable', 'exists:doctors,id'], // يمكن تحديث الطبيب إذا كان المدير
                'prescription_date' => ['required', 'date', 'before_or_equal:today'],
                'notes' => ['nullable', 'string'],
                'medicines' => ['required', 'array', 'min:1'],
                'medicines.*.medicine_id' => ['required', 'exists:medicines,id'],
                'medicines.*.dosage' => ['required', 'string', 'max:255'],
                'medicines.*.frequency' => ['required', 'string', 'max:255'],
                'medicines.*.duration' => ['nullable', 'string', 'max:255'],
                'medicines.*.instructions' => ['nullable', 'string'],
            ]);

            DB::beginTransaction();

            $prescription->update([
                'patient_id' => $request->patient_id,
                'doctor_id' => $request->doctor_id, // يمكن تحديثه يدوياً إذا كان المدير يغيره
                'prescription_date' => $request->prescription_date,
                'notes' => $request->notes,
            ]);

            // مزامنة الأدوية المرفقة بالوصفة
            $medicinesToSync = [];
            foreach ($request->medicines as $med) {
                $medicinesToSync[$med['medicine_id']] = [
                    'dosage' => $med['dosage'],
                    'frequency' => $med['frequency'],
                    'duration' => $med['duration'] ?? null,
                    'instructions' => $med['instructions'] ?? null,
                ];
            }
            $prescription->medicines()->sync($medicinesToSync);

            DB::commit();

            return response()->json([
                'message' => 'تم تحديث وصفة الدواء بنجاح.',
                'prescription' => $prescription->load('patient.user', 'doctor.user', 'medicines'),
                'status' => 'success'
            ]);

        } catch (ValidationException $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'خطأ في التحقق من صحة البيانات.',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'حدث خطأ غير متوقع: ' . $e->getMessage(),
                'status' => 'error'
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Prescription  $prescription
     * @return \Illuminate\Http\JsonResponse
     */
   public function destroy($id)
{
    // جلب الوصفة بناءً على الـ ID
    $prescription = Prescription::findOrFail($id);

    // يمكن للمدير , الصيدلي  حذف وصفات الأدوية
    $this->authorize('delete', $prescription);

    try {
        DB::beginTransaction();

        // فصل الأدوية المرتبطة أولاً
        $prescription->medicines()->detach();

        // حذف الوصفة
        $prescription->delete();

        DB::commit();

        return response()->json([
            'message' => 'تم حذف وصفة الدواء بنجاح.',
            'status' => 'success'
        ], 200);
    } catch (\Exception $e) {
        DB::rollBack();
        return response()->json([
            'message' => 'حدث خطأ أثناء حذف وصفة الدواء: ' . $e->getMessage(),
            'status' => 'error'
        ], 500);
    }
}

}
