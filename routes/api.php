<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\RoomController;
use App\Http\Controllers\Api\TaskController;
use App\Http\Controllers\Api\NurseController;
use App\Http\Controllers\Api\DoctorController;
use App\Http\Controllers\Api\InvoiceController;
use App\Http\Controllers\Api\LabTestController;
use App\Http\Controllers\Api\PatientController;
use App\Http\Controllers\Api\MedicineController;
use App\Http\Controllers\Api\DepartmentController;
use App\Http\Controllers\Api\AppointmentController;
use App\Http\Controllers\Api\PrescriptionController;
use App\Http\Controllers\Api\MedicalRecordController;
use App\Http\Controllers\Api\InventoryController;
use App\Http\Controllers\TipController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// مسار افتراضي للمستخدم المصادق عليه
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user()->load('role');
});

// مسارات المصادقة (لا تتطلب مصادقة للوصول إليها)
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// مسار تسجيل الخروج (يتطلب مصادقة)
Route::middleware('auth:sanctum')->post('/logout', [AuthController::class, 'logout']);

// --------------------- مسار التحقق من كلمة السر ------------------
Route::post('/verify-club', [AuthController::class, 'verifyClub']);

 // -------------------- مسارات النصائح --------------------
Route::middleware(['auth:sanctum', 'role:admin,doctor'])->group(function () {
    Route::post('/tips', [TipController::class, 'store']);
    Route::put('/tips/{tip}', [TipController::class, 'update']);
    Route::delete('/tips/{tip}', [TipController::class, 'destroy']);
});

Route::get('/tips', [TipController::class, 'index']); // متاح للجميع
Route::get('/tips/{tip}', [TipController::class, 'show']); // متاح للجميع

// ------------------ مسار الحصول على الإشعارات للمستخدم المصادق عليه  -------------------
Route::middleware('auth:sanctum')->get('/notifications', function () {
    return auth()->user()->notifications;
});


// مسارات إدارة الأقسام (تتطلب مصادقة ودور "admin")
Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {
    Route::apiResource('departments', DepartmentController::class);
});

Route::get('/departments', [DepartmentController::class, 'index']);

// مسارات الأطباء
Route::get('/doctors', [DoctorController::class, 'index']); // يمكن للجميع رؤية الأطباء
Route::get('/doctors/{doctor}', [DoctorController::class, 'show']); // يمكن للجميع رؤية تفاصيل طبيب

// مسارات إدارة الأطباء (تتطلب مصادقة ودور "admin")
Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {
    Route::post('/doctors', [DoctorController::class, 'store']);
    Route::put('/doctors/{doctor}', [DoctorController::class, 'update']);
    Route::delete('/doctors/{doctor}', [DoctorController::class, 'destroy']);
});

// مسارات المرضى
Route::middleware('auth:sanctum')->group(function () {
    // يمكن للمدير وموظف الاستقبال رؤية قائمة المرضى
    Route::get('/patients', [PatientController::class, 'index'])->middleware('role:admin,receptionist,doctor,pharmacist');
    // يمكن للمدير وموظف الاستقبال إنشاء مرضى جدد      صار ممكن للمرض كمان AYA
    Route::post('/patients', [PatientController::class, 'store'])->middleware('role:admin,receptionist,doctor,nurse,pharmacist');
    // يمكن للمدير، موظف الاستقبال، أو المريض نفسه رؤية تفاصيله
    Route::get('/patients/{patient}', [PatientController::class, 'show']);
    // يمكن للمدير أو المريض نفسه تحديث بياناته AYA
   Route::put('/patients/{patient}', [PatientController::class, 'update'])->middleware('role:admin,patient,pharmacist');
    // يمكن للمدير و للممرض  حذف المرضى  AYA
    Route::delete('/patients/{patient}', [PatientController::class, 'destroy'])->middleware('role:admin|nurse');
});

// مسارات المواعيد (تتطلب مصادقة لمعظم العمليات)
Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('appointments', AppointmentController::class);

    // مسار خاص للممرض لتحديث حالة الموعد
    // الممرض يمكنه تحديث حالة الموعد (قبول/رفض)
    Route::put('/appointments/{appointmentId}/status-by-nurse', [NurseController::class, 'updateAppointmentStatus'])
        ->middleware('role:nurse'); // فقط للممرضين
});

// مسارات الغرف (تتطلب مصادقة)
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/rooms', [RoomController::class, 'index'])->middleware('role:admin,receptionist');
    Route::post('/rooms', [RoomController::class, 'store'])->middleware('role:admin'); // فقط المدير يمكنه إضافة
    Route::get('/rooms/{room}', [RoomController::class, 'show'])->middleware('role:admin,receptionist');
    Route::put('/rooms/{room}', [RoomController::class, 'update'])->middleware('role:admin,receptionist');
    Route::delete('/rooms/{room}', [RoomController::class, 'destroy'])->middleware('role:admin'); // فقط المدير يمكنه حذف
});

// مسارات الفواتير (تتطلب مصادقة)
Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('invoices', InvoiceController::class);
});

// مسارات الأدوية (تتطلب مصادقة)
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/medicines', [MedicineController::class, 'index'])->middleware('role:admin,accountant,receptionist,pharmacist');
    Route::post('/medicines', [MedicineController::class, 'store'])->middleware('role:admin,pharmacist');
    Route::get('/medicines/{medicine}', [MedicineController::class, 'show'])->middleware('role:admin,accountant,receptionist,pharmacist');
    Route::put('/medicines/{medicine}', [MedicineController::class, 'update'])->middleware('role:admin,pharmacist');
    Route::delete('/medicines/{medicine}', [MedicineController::class, 'destroy'])->middleware('role:admin,pharmacist');
});

// مسارات المهام (تتطلب مصادقة)
Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('tasks', TaskController::class);
});

// مسارات السجلات الطبية (تتطلب مصادقة)
Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('medical-records', MedicalRecordController::class);
});

// مسارات التحاليل المخبرية (تتطلب مصادقة)
Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('lab-tests', LabTestController::class);
});

// مسارات وصفات الأدوية (تتطلب مصادقة)
Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('prescriptions', PrescriptionController::class);
});

// -------------- مسار الوصفات الطبية للصيدلي -------------
Route::middleware(['auth:sanctum', 'role:pharmacist'])->get('/prescriptions', [PrescriptionController::class, 'index']);
Route::middleware(['auth:sanctum', 'role:pharmacist'])->post('/prescriptions', [PrescriptionController::class, 'store']);

// ------------ مسار صرف الدواء ------------------
Route::post('/inventory/dispense-from-prescription', [InventoryController::class, 'dispenseFromPrescription']);


// --- مسارات الممرضين الجديدة والمعدلة ---

// يمكن للجميع رؤية قائمة الممرضين وتفاصيل ممرض واحد
Route::get('/nurses', [NurseController::class, 'index']);
Route::get('/nurses/{nurse}', [NurseController::class, 'show']);

// مسارات إدارة الممرضين (تتطلب مصادقة ودور "admin")
Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {
    Route::post('/nurses', [NurseController::class, 'store']);
    Route::put('/nurses/{nurse}', [NurseController::class, 'update']); // المدير يمكنه تحديث أي ممرض
    Route::delete('/nurses/{nurse}', [NurseController::class, 'destroy']);
});

// مسار خاص للممرض لتحديث ملفه الشخصي
// الممرض نفسه يمكنه تحديث معلومات ملفه الشخصي (باستخدام نفس مسار PUT ولكن بmiddleware مختلف)
Route::middleware(['auth:sanctum', 'role:nurse'])->group(function () {
    // هذا المسار يسمح للممرض بتحديث ملفه الشخصي بناءً على user_id الخاص به
    // يجب أن يتعامل NurseController@update مع صلاحية الممرض لتعديل ملفه الخاص
    Route::put('/nurses/my-profile', [NurseController::class, 'updateSelf']); // سنضيف هذه الدالة للكنترولر
});

// مسار للحصول على المرضى الذين تمت معالجة مواعيدهم بواسطة ممرض معين
// يمكن للمدير أو الممرض نفسه رؤية هذه القائمة
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/nurses/{nurseId}/processed-patients', [NurseController::class, 'getPatients'])
        ->middleware('role:admin,nurse');
});

Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {
    Route::apiResource('inventory', InventoryController::class);
});
    Route::apiResource('inventory', InventoryController::class);

// مسار API للاختبار (يمكنك إزالته لاحقاً)
Route::get('/test-api', function () {
    return response()->json([
        'message' => 'مرحباً من API مستشفى الدكتور فرزات أيوب!',
        'status' => 'success'
    ]);
});

