<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Patient;
use App\Models\Doctor;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AppointmentController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        // يمكن للمدير وموظف الاستقبال رؤية جميع المواعيد
        // الطبيب يرى مواعيده فقط، والمريض يرى مواعيده فقط
        $user = Auth::user();
        $appointments = collect(); // كائن مجموعة فارغ

        if ($user->role->name === 'admin' || $user->role->name === 'receptionist') {
            $appointments = Appointment::with('patient.user', 'doctor.user')->get();
        } elseif ($user->role->name === 'doctor') {
            $doctor = $user->doctor;
            if ($doctor) {
                $appointments = $doctor->appointments()->with('patient.user', 'doctor.user')->get();
            }
        } elseif ($user->role->name === 'patient') {
            $patient = $user->patient;
            if ($patient) {
                $appointments = $patient->appointments()->with('patient.user', 'doctor.user')->get();
            }
        } else {
            return response()->json([
                'message' => 'ليس لديك الصلاحيات الكافية لعرض المواعيد.',
                'status' => 'error'
            ], 403);
        }

        return response()->json([
            'message' => 'تم جلب المواعيد بنجاح.',
            'appointments' => $appointments,
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
    $this->authorize('create', Appointment::class);

    try {
        $user = Auth::user();

        $rules = [
            'doctor_id' => ['required', 'exists:doctors,id'],
            'appointment_date' => ['required', 'date_format:Y-m-d H:i:s', 'after_or_equal:now'],
            'status' => ['nullable', 'string', 'in:pending,confirmed,cancelled,completed'],
            'notes' => ['nullable', 'string'],
        ];

        // لو المستخدم مدير أو موظف استقبال، لازم يحدد المريض
        if ($user->role->name === 'admin' || $user->role->name === 'receptionist') {
            $rules['patient_id'] = ['required', 'exists:patients,id'];
        }

        $request->validate($rules);

        // لو المستخدم مريض، ناخذ الـ patient_id من الـ Auth مباشرة
        if ($user->role->name === 'patient') {
            $patient = $user->patient;
            if (!$patient) {
                return response()->json([
                    'message' => 'لا يوجد ملف مريض مرتبط بهذا المستخدم.',
                    'status' => 'error'
                ], 403);
            }
            $patientId = $patient->id;
        } else {
            // المدير أو موظف الاستقبال يحددوا المريض
            $patientId = $request->patient_id;
        }

        // إنشاء الموعد
        $appointment = Appointment::create([
            'patient_id' => $patientId,
            'doctor_id' => $request->doctor_id,
            'appointment_date' => $request->appointment_date,
            'status' => $request->status ?? 'pending',
            'notes' => $request->notes,
        ]);

        return response()->json([
            'message' => 'تم إنشاء الموعد بنجاح.',
            'appointment' => $appointment->load('patient.user', 'doctor.user'),
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
     * @param  \App\Models\Appointment  $appointment
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Appointment $appointment)
    {
        // يمكن للمدير، موظف الاستقبال، الطبيب المرتبط، أو المريض المرتبط رؤية تفاصيل الموعد
        $this->authorize('view', $appointment);

        return response()->json([
            'message' => 'تم جلب الموعد بنجاح.',
            'appointment' => $appointment->load('patient.user', 'doctor.user'),
            'status' => 'success'
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Appointment  $appointment
     * @return \Illuminate\Http\JsonResponse
     */
public function update(Request $request, Appointment $appointment)
{
    // يمكن للمدير، موظف الاستقبال، أو الطبيب المرتبط تحديث الموعد
    // $this->authorize('update', $appointment);

    try {
        $user = Auth::user();

        $rules = [
            'doctor_id' => ['required', 'exists:doctors,id'],
            'appointment_date' => ['required', 'date_format:Y-m-d H:i:s', 'after_or_equal:now'],
            'status' => ['required', 'string', 'in:pending,confirmed,cancelled,completed'],
            'notes' => ['nullable', 'string'],
        ];

        // المدير أو موظف الاستقبال لازم يبعث patient_id
        if ($user->role->name === 'admin' || $user->role->name === 'receptionist') {
            $rules['patient_id'] = ['required', 'exists:patients,id'];
        }

        $request->validate($rules);

        // تحديد الـ patient_id حسب نوع المستخدم
        if ($user->role->name === 'patient') {
            // المريض ما بيقدر يغير patient_id → نخليه مثل ما هو
            $patientId = $appointment->patient_id;
        } else {
            $patientId = $request->patient_id;
        }

        $appointment->update([
            'patient_id' => $patientId,
            'doctor_id' => $request->doctor_id,
            'appointment_date' => $request->appointment_date,
            'status' => $request->status,
            'notes' => $request->notes,
        ]);

        return response()->json([
            'message' => 'تم تحديث الموعد بنجاح.',
            'appointment' => $appointment->load('patient.user', 'doctor.user'),
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
     * @param  \App\Models\Appointment  $appointment
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Appointment $appointment)
    {
        // يمكن للمدير أو موظف الاستقبال فقط حذف المواعيد
        $this->authorize('delete', $appointment);

        try {
            $appointment->delete();

            return response()->json([
                'message' => 'تم حذف الموعد بنجاح.',
                'status' => 'success'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'حدث خطأ أثناء حذف الموعد: ' . $e->getMessage(),
                'status' => 'error'
            ], 500);
        }
    }
}
