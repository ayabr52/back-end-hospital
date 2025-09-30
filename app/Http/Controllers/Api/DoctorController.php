<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Doctor;
use App\Models\User;
use App\Models\Role;
use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage; // استيراد Storage Facade

class DoctorController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $doctors = Doctor::with('department', 'user')->take(8)->get();
        return response()->json([
            'message' => 'تم جلب الأطباء بنجاح.',
            'doctors' => $doctors,
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
        $this->authorize('create', Doctor::class);

        try {
            // التحقق من صحة البيانات
            $request->validate([
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
                'password' => ['required', 'string', 'min:8', 'confirmed'],
                'specialty' => ['required', 'string', 'max:255'],
                'bio' => ['nullable', 'string'],
                'image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,svg', 'max:2048'], // 'image' للتحقق من أنه ملف صورة
                'department_id' => ['nullable', 'exists:departments,id'],
                'phone' => ['nullable', 'string', 'max:20'],
                'national_id' => ['nullable', 'string', 'max:20', 'unique:users'],
                'address' => ['nullable', 'string', 'max:255'],
                'dob' => ['nullable', 'date'],
                'gender' => ['nullable', 'string', 'in:male,female'],
            ]);

            DB::beginTransaction();

            $doctorRole = Role::where('name', 'doctor')->first();

            if (!$doctorRole) {
                DB::rollBack();
                return response()->json([
                    'message' => 'دور الطبيب غير موجود. يرجى تشغيل Seeders.',
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
                'role_id' => $doctorRole->id,
            ]);

            $imagePath = null;
            // معالجة رفع الصورة
            if ($request->hasFile('image')) {
                // حفظ الصورة في مجلد 'public/doctors_images'
                // تأكد أن لديك symlink من public/storage إلى storage/app/public
                // يمكنك إنشاء symlink باستخدام: php artisan storage:link
                $imagePath = $request->file('image')->store('doctors_images', 'public');
                // الحصول على الرابط العام للصورة
                $imagePath = Storage::url($imagePath);
            }

            $doctor = Doctor::create([
                'user_id' => $user->id,
                'name' => $request->name,
                'specialty' => $request->specialty,
                'bio' => $request->bio,
                'image' => $imagePath, // تخزين رابط الصورة
                'department_id' => $request->department_id,
            ]);

            DB::commit();

            return response()->json([
                'message' => 'تم إنشاء الطبيب بنجاح.',
                'doctor' => $doctor->load('user', 'department'),
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
     * @param  \App\Models\Doctor  $doctor
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Doctor $doctor)
    {
        return response()->json([
            'message' => 'تم جلب الطبيب بنجاح.',
            'doctor' => $doctor->load('user', 'department'),
            'status' => 'success'
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Doctor  $doctor
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, Doctor $doctor)
    {
        $this->authorize('update', $doctor);

        try {
            $request->validate([
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $doctor->user_id],
                'specialty' => ['required', 'string', 'max:255'],
                'bio' => ['nullable', 'string'],
                // تغيير قاعدة التحقق للسماح بملف صورة
                'image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,svg', 'max:2048'], // 'image' للتحقق من أنه ملف صورة
                'department_id' => ['nullable', 'exists:departments,id'],
                'phone' => ['nullable', 'string', 'max:20'],
                'national_id' => ['nullable', 'string', 'max:20', 'unique:users,national_id,' . $doctor->user_id],
                'address' => ['nullable', 'string', 'max:255'],
                'dob' => ['nullable', 'date'],
                'gender' => ['nullable', 'string', 'in:male,female'],
            ]);

            DB::beginTransaction();

            // تحديث بيانات المستخدم المرتبطة
            $doctor->user->update([
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'national_id' => $request->national_id,
                'address' => $request->address,
                'dob' => $request->dob,
                'gender' => $request->gender,
            ]);

            $imagePath = $doctor->image; // احتفظ بالرابط الحالي إذا لم يتم رفع صورة جديدة
            // معالجة رفع الصورة في التحديث
            if ($request->hasFile('image')) {
                // حذف الصورة القديمة إذا كانت موجودة
                if ($doctor->image && Storage::disk('public')->exists(str_replace('/storage/', '', $doctor->image))) {
                    Storage::disk('public')->delete(str_replace('/storage/', '', $doctor->image));
                }
                // حفظ الصورة الجديدة
                $imagePath = $request->file('image')->store('doctors_images', 'public');
                $imagePath = Storage::url($imagePath);
            } elseif ($request->input('image_cleared')) { // إذا أرسلت الواجهة الأمامية إشارة لمسح الصورة
                if ($doctor->image && Storage::disk('public')->exists(str_replace('/storage/', '', $doctor->image))) {
                    Storage::disk('public')->delete(str_replace('/storage/', '', $doctor->image));
                }
                $imagePath = null;
            }


            // تحديث بيانات الطبيب
            $doctor->update([
                'name' => $request->name,
                'specialty' => $request->specialty,
                'bio' => $request->bio,
                'image' => $imagePath, // تخزين رابط الصورة الجديد/المحدث
                'department_id' => $request->department_id,
            ]);

            DB::commit();

            return response()->json([
                'message' => 'تم تحديث الطبيب بنجاح.',
                'doctor' => $doctor->load('user', 'department'),
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
     * @param  \App\Models\Doctor  $doctor
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Doctor $doctor)
    {
        $this->authorize('delete', $doctor);

        try {
            DB::beginTransaction();

            // حذف الصورة المرتبطة بالطبيب من التخزين
            if ($doctor->image) {
                // استخراج المسار النسبي للملف من الرابط الكامل
                $filePath = str_replace('/storage/', '', $doctor->image);
                if (Storage::disk('public')->exists($filePath)) {
                    Storage::disk('public')->delete($filePath);
                }
            }

            $doctor->user->delete();
            $doctor->delete();
            DB::commit();

            return response()->json([
                'message' => 'تم حذف الطبيب وحسابه بنجاح.',
                'status' => 'success'
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'حدث خطأ أثناء حذف الطبيب: ' . $e->getMessage(),
                'status' => 'error'
            ], 500);
        }
    }
}
