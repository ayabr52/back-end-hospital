<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Role; // استيراد موديل Role
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use App\Models\Patient;
class AuthController extends Controller
{
    /**
     * Register new user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
public function register(Request $request)
{
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

        // الحصول على دور المريض الافتراضي
        $patientRole = Role::where('name', 'patient')->first();

        if (!$patientRole) {
            return response()->json([
                'message' => 'دور المريض غير موجود. يرجى تشغيل Seeders.',
                'status' => 'error'
            ], 500);
        }

        // إنشاء المستخدم
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

        // إنشاء سجل المريض تلقائياً
        Patient::create([
            'user_id' => $user->id,
            'name' => $user->name,
            'phone' => $user->phone,
            'national_id' => $user->national_id,
            'address' => $user->address,
            'dob' => $user->dob,
            'gender' => $user->gender,
               'favorite_club' => $request->favorite_club, //  النادي المفضل
]);


        // إنشاء رمز مميز (token) للمستخدم
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'تم تسجيل المستخدم والمريض بنجاح.',
            'user' => $user,
            'access_token' => $token,
            'token_type' => 'Bearer',
            'role' => $user->role->name
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
     * Authenticate user and return a token.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        try {
            $request->validate([
                'email' => 'required|email',
                'password' => 'required',
            ]);

            $user = User::where('email', $request->email)->first();

            if (!$user || !Hash::check($request->password, $user->password)) {
                return response()->json([
                    'message' => 'بيانات الاعتماد غير صحيحة.',
                    'status' => 'error'
                ], 401);
            }

            // حذف الرموز المميزة القديمة للمستخدم (اختياري، يمكن الاحتفاظ بها)
            // $user->tokens()->delete();

            // إنشاء رمز مميز جديد
            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'message' => 'تم تسجيل الدخول بنجاح.',
                'user' => $user,
                'access_token' => $token,
                'token_type' => 'Bearer',
                'role' => $user->role->name // إرجاع اسم الدور
            ], 200);

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
     * Logout user (revoke token).
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout(Request $request)
{
    $token = $request->user()->currentAccessToken();

    if ($token) {
        $token->delete();
    }

    return response()->json([
        'message' => 'تم تسجيل الخروج بنجاح.',
        'status' => 'success'
    ], 200);
}



  // التحقق حسب جدول النادي المفضل
   public function verifyClub(Request $request)
{
    $request->validate([
        'email' => 'required|email',
        'favorite_club' => 'required|string',
    ]);

    $user = User::where('email', $request->email)->first();

    if (!$user) {
        return response()->json(['message' => 'المستخدم غير موجود'], 404);
    }

    $profile = $user->doctor ?? $user->nurse ?? $user->patient ;

    if ($profile && $profile->favorite_club === $request->favorite_club) {
        return response()->json(['message' => 'تم التحقق بنجاح']);
    }

    return response()->json(['message' => 'اسم النادي غير صحيح'], 403);
}


}
