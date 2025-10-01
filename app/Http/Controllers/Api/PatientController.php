<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Patient;
use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class PatientController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        // يمكن للمدير وموظف الاستقبال رؤية قائمة المرضى
        $this->authorize('viewAny', Patient::class);

        $patients = Patient::with('user')->get();
        return response()->json([
            'message' => 'تم جلب المرضى بنجاح.',
            'patients' => $patients,
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
        // يمكن للمدير وموظف الاستقبال إنشاء مرضى جدد
        $this->authorize('create', Patient::class);

        try {
            $request->validate([
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
                'password' => ['required', 'string', 'min:8', 'confirmed'],
                'favorite_club' => ['nullable', 'string', 'max:255'],
//
                'phone' => ['nullable', 'string', 'max:20'],
                'national_id' => ['nullable', 'string', 'max:20', 'unique:users'],
                'address' => ['nullable', 'string', 'max:255'],
                'dob' => ['nullable', 'date'],
                'gender' => ['nullable', 'string', 'in:male,female'],
            ]);

            DB::beginTransaction();

            $patientRole = Role::where('name', 'patient')->first();

            if (!$patientRole) {
                DB::rollBack();
                return response()->json([
                    'message' => 'دور المريض غير موجود. يرجى تشغيل Seeders.',
                    'status' => 'error'
                ], 500);
            }

            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'phone' => $request->phone,
                'national_id' => $request->national_id,
                'address' => $request->address,
                'dob' => $request->dob,
                'gender' => $request->gender,
                'role_id' => $patientRole->id,
            ]);

            $patient = Patient::create([
                'user_id' => $user->id,
                'name' => $request->name,
                'phone' => $request->phone,
                'national_id' => $request->national_id,
                'address' => $request->address,
                'dob' => $request->dob,
                'gender' => $request->gender,
                 'favorite_club' => $request->favorite_club, //  النادي المفضل
]);


            DB::commit();

            return response()->json([
                'message' => 'تم إنشاء المريض بنجاح.',
                'patient' => $patient->load('user'),
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
     * @param  \App\Models\Patient  $patient
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Patient $patient)
    {
        // يمكن للمدير، موظف الاستقبال، أو المريض نفسه رؤية تفاصيله
        $this->authorize('view', $patient);

        return response()->json([
            'message' => 'تم جلب المريض بنجاح.',
            'patient' => $patient->load('user'),
            'status' => 'success'
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Patient  $patient
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, Patient $patient)
    {
        // يمكن للمدير أو المريض نفسه تحديث بياناته
        $this->authorize('update', $patient);

        try {
            $request->validate([
                'name' => ['required', 'string', 'max:255'],
                'favorite_club' => ['nullable', 'string', 'max:255'],
//
                'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $patient->user_id],
                'phone' => ['nullable', 'string', 'max:20'],
                'national_id' => ['nullable', 'string', 'max:20', 'unique:users,national_id,' . $patient->user_id],
                'address' => ['nullable', 'string', 'max:255'],
                'dob' => ['nullable', 'date'],
                'gender' => ['nullable', 'string', 'in:male,female'],
            ]);

            DB::beginTransaction();

            // تحديث بيانات المستخدم المرتبطة
            $patient->user->update([
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'national_id' => $request->national_id,
                'address' => $request->address,
                'dob' => $request->dob,
                'gender' => $request->gender,
                 
]);


            // تحديث بيانات المريض
            $patient->update([
                'name' => $request->name,
                'phone' => $request->phone,
                'national_id' => $request->national_id,
                'address' => $request->address,
                'dob' => $request->dob,
                'gender' => $request->gender,
                 'favorite_club' => $request->favorite_club, //  النادي المفضل
]);


            DB::commit();

            return response()->json([
                'message' => 'تم تحديث المريض بنجاح.',
                'patient' => $patient->load('user'),
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
     * @param  \App\Models\Patient  $patient
     * @return \Illuminate\Http\JsonResponse
     */
    // هون بدهم كمان الممرض يحذف المرضى مش بس المدير ِ AYA
    public function destroy(Patient $patient)
    {
        // يمكن للمدير فقط حذف المرضى
        $this->authorize('delete', $patient);

        try {
            DB::beginTransaction();
            $patient->user->delete(); // حذف حساب المستخدم المرتبط
            $patient->delete(); // حذف سجل المريض
            DB::commit();

            return response()->json([
                'message' => 'تم حذف المريض وحسابه بنجاح.',
                'status' => 'success'
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'حدث خطأ أثناء حذف المريض: ' . $e->getMessage(),
                'status' => 'error'
            ], 500);
        }
    }
}
