<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Nurse;
use App\Models\User;
use App\Models\Role;
use App\Models\Department;
use App\Models\Appointment;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage; // استيراد كلاس Storage للتعامل مع الملفات

class NurseController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $nurses = Nurse::with('user', 'department')->get();
        return response()->json([
            'message' => 'تم جلب الممرضين بنجاح.',
            'nurses' => $nurses,
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
        if (!Auth::check() || !Auth::user()->hasRole('admin')) {
            return response()->json(['message' => 'غير مصرح لك بإنشاء ممرضين.', 'status' => 'error'], 403);
        }

        try {
            $request->validate([
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
                'password' => ['required', 'string', 'min:8', 'confirmed'],
                'phone' => ['nullable', 'string', 'max:20'],
                'national_id' => ['nullable', 'string', 'max:20', 'unique:users'],
                'address' => ['nullable', 'string', 'max:255'],
                'dob' => ['nullable', 'date'],
                'gender' => ['nullable', 'string', 'in:male,female'],
                'specialty' => ['nullable', 'string', 'max:255'],
                'bio' => ['nullable', 'string'],
                'image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,svg', 'max:2048'], // تم التعديل: أصبح 'image' ملفًا
                'department_id' => ['nullable', 'exists:departments,id'],
            ]);

            DB::beginTransaction();

            $nurseRole = Role::where('name', 'nurse')->first();

            if (!$nurseRole) {
                DB::rollBack();
                return response()->json([
                    'message' => 'دور الممرض غير موجود. يرجى تشغيل Seeders.',
                    'status' => 'error'
                ], 500);
            }

            // إنشاء حساب المستخدم أولاً
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'phone' => $request->phone,
                'national_id' => $request->national_id,
                'address' => $request->address,
                'dob' => $request->dob,
                'gender' => $request->gender,
                'role_id' => $nurseRole->id,
            ]);

            $imagePath = null;
            // التعامل مع رفع الصورة
            if ($request->hasFile('image')) {
                $imagePath = $request->file('image')->store('public/nurses_images');
                $imagePath = Storage::url($imagePath); // تحويل المسار إلى URL عام
            }

            // إنشاء ملف تعريف الممرض المرتبط بالمستخدم
            $nurse = Nurse::create([
                'user_id' => $user->id,
                'name' => $request->name,
                'specialty' => $request->specialty,
                'bio' => $request->bio,
                'image' => $imagePath, // حفظ مسار الصورة في قاعدة البيانات
                'department_id' => $request->department_id,
            ]);

            DB::commit();

            return response()->json([
                'message' => 'تم إنشاء الممرض بنجاح.',
                'nurse' => $nurse->load('user', 'department'),
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
     * @param  \App\Models\Nurse  $nurse
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Nurse $nurse)
    {
        return response()->json([
            'message' => 'تم جلب الممرض بنجاح.',
            'nurse' => $nurse->load('user', 'department'),
            'status' => 'success'
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Nurse  $nurse
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, Nurse $nurse)
    {
        if (!Auth::check() || (!Auth::user()->hasRole('admin') && Auth::user()->id !== $nurse->user_id)) {
            return response()->json(['message' => 'غير مصرح لك بتعديل معلومات هذا الممرض.', 'status' => 'error'], 403);
        }

        try {
            $request->validate([
                'name' => ['sometimes', 'required', 'string', 'max:255'],
                'email' => ['sometimes', 'required', 'string', 'email', 'max:255', 'unique:users,email,' . $nurse->user_id],
                'password' => ['nullable', 'string', 'min:8', 'confirmed'],
                'phone' => ['nullable', 'string', 'max:20'],
                'national_id' => ['nullable', 'string', 'max:20', 'unique:users,national_id,' . $nurse->user_id],
                'address' => ['nullable', 'string', 'max:255'],
                'dob' => ['nullable', 'date'],
                'gender' => ['nullable', 'string', 'in:male,female'],
                'specialty' => ['nullable', 'string', 'max:255'],
                'bio' => ['nullable', 'string'],
                'image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,svg', 'max:2048'], // تم التعديل: أصبح 'image' ملفًا
                'department_id' => ['nullable', 'exists:departments,id'],
            ]);

            DB::beginTransaction();

            // تحديث تفاصيل المستخدم
            $user = $nurse->user;
            if ($user) {
                $user->name = $request->input('name', $user->name);
                $user->email = $request->input('email', $user->email);
                if ($request->filled('password')) {
                    $user->password = Hash::make($request->password);
                }
                $user->phone = $request->input('phone', $user->phone);
                $user->national_id = $request->input('national_id', $user->national_id);
                $user->address = $request->input('address', $user->address);
                $user->dob = $request->input('dob', $user->dob);
                $user->gender = $request->input('gender', $user->gender);
                $user->save();
            }

            // التعامل مع تحديث الصورة
            $imagePath = $nurse->image; // احتفظ بالمسار الحالي افتراضيًا
            if ($request->hasFile('image')) {
                // حذف الصورة القديمة إذا وجدت
                if ($nurse->image) {
                    Storage::delete(str_replace('/storage/', 'public/', $nurse->image));
                }
                $imagePath = $request->file('image')->store('public/nurses_images');
                $imagePath = Storage::url($imagePath);
            } elseif ($request->input('image') === null && $nurse->image) {
                // إذا تم إرسال 'image' كـ null صراحةً، فهذا يعني حذف الصورة
                Storage::delete(str_replace('/storage/', 'public/', $nurse->image));
                $imagePath = null;
            }


            // تحديث تفاصيل ملف تعريف الممرض
            $nurse->update([
                'name' => $request->input('name', $nurse->name),
                'specialty' => $request->input('specialty', $nurse->specialty),
                'bio' => $request->input('bio', $nurse->bio),
                'image' => $imagePath, // حفظ مسار الصورة الجديد/المحدث
                'department_id' => $request->input('department_id', $nurse->department_id),
            ]);

            DB::commit();

            return response()->json([
                'message' => 'تم تحديث معلومات الممرض بنجاح.',
                'nurse' => $nurse->load('user', 'department'),
                'status' => 'success'
            ], 200);

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
     * @param  \App\Models\Nurse  $nurse
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Nurse $nurse)
    {
        if (!Auth::check() || !Auth::user()->hasRole('admin')) {
            return response()->json(['message' => 'غير مصرح لك بحذف ممرضين.', 'status' => 'error'], 403);
        }

        DB::beginTransaction();
        try {
            // حذف الصورة المرتبطة إذا وجدت
            if ($nurse->image) {
                Storage::delete(str_replace('/storage/', 'public/', $nurse->image));
            }

            $user = $nurse->user;
            $nurse->delete();
            if ($user) {
                $user->delete();
            }
            DB::commit();
            return response()->json(['message' => 'تم حذف الممرض بنجاح.', 'status' => 'success'], 204);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'حدث خطأ أثناء حذف الممرض: ' . $e->getMessage(),
                'status' => 'error'
            ], 500);
        }
    }

    /**
     * Get all patients associated with a specific nurse.
     *
     * @param  int  $nurseId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getPatients($nurseId)
    {
        $nurse = Nurse::find($nurseId);

        if (!$nurse) {
            return response()->json(['message' => 'الممرض غير موجود.'], 404);
        }

        $patients = $nurse->processedAppointments()->with('patient')->get()->map(function($appointment) {
            return $appointment->patient;
        })->unique('id')->values();

        return response()->json([
            'message' => 'تم جلب المرضى المرتبطين بالممرض بنجاح.',
            'patients' => $patients,
            'status' => 'success'
        ], 200);
    }

    /**
     * Update appointment status (confirm/reject) by a nurse.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $appointmentId
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateAppointmentStatus(Request $request, $appointmentId)
    {
        $appointment = Appointment::find($appointmentId);

        if (!$appointment) {
            return response()->json(['message' => 'الموعد غير موجود.'], 404);
        }

        if (!Auth::check() || !Auth::user()->hasRole('nurse')) {
            return response()->json(['message' => 'غير مصرح لك بتحديث حالة الموعد. فقط الممرضين يمكنهم القيام بذلك.', 'status' => 'error'], 403);
        }

        $nurse = Auth::user()->nurse;
        if (!$nurse) {
            return response()->json(['message' => 'لا يوجد ملف تعريف ممرض مرتبط بحسابك.', 'status' => 'error'], 403);
        }

        try {
            $validatedData = $request->validate([
                'status' => 'required|in:confirmed,rejected',
            ]);

            if ($appointment->status !== 'pending') {
                return response()->json([
                    'message' => 'لا يمكن تحديث حالة موعد تم معالجته بالفعل.',
                    'current_status' => $appointment->status
                ], 400);
            }

            $appointment->status = $validatedData['status'];
            $appointment->processed_by_nurse_id = $nurse->id;
            $appointment->processed_at = now();
            $appointment->save();

            return response()->json([
                'message' => 'تم تحديث حالة الموعد بنجاح.',
                'appointment' => $appointment->load('patient', 'doctor', 'processedByNurse'),
                'status' => 'success'
            ], 200);

        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'خطأ في التحقق من صحة البيانات.',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'حدث خطأ غير متوقع أثناء تحديث حالة الموعد: ' . $e->getMessage(),
                'status' => 'error'
            ], 500);
        }
    }
}
