<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Task; // استيراد موديل Task
use App\Models\User; // استيراد موديل User
use App\Models\Patient; // استيراد موديل Patient
use App\Models\Department; // استيراد موديل Department
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Auth; // لاستخدام المستخدم المصادق عليه

class TaskController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        // يمكن للمدير رؤية جميع المهام
        // الطبيب/الممرض/موظف الاستقبال يرى المهام المكلف بها أو التي كلفها
        $user = Auth::user();
        $tasks = collect();

        if ($user->role->name === 'admin') {
            $tasks = Task::with('assignedTo.role', 'assignedBy.role', 'patient.user', 'department')->get();
        } elseif (in_array($user->role->name, ['doctor', 'nurse', 'receptionist'])) {
            $tasks = Task::where('assigned_to_user_id', $user->id)
                         ->orWhere('assigned_by_user_id', $user->id)
                         ->with('assignedTo.role', 'assignedBy.role', 'patient.user', 'department')
                         ->get();
        } else {
            return response()->json([
                'message' => 'ليس لديك الصلاحيات الكافية لعرض المهام.',
                'status' => 'error'
            ], 403);
        }

        return response()->json([
            'message' => 'تم جلب المهام بنجاح.',
            'tasks' => $tasks,
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
        // يمكن للمدير، الطبيب، موظف الاستقبال إنشاء مهام
        $this->authorize('create', Task::class);

        try {
            $request->validate([
                'title' => ['required', 'string', 'max:255'],
                'description' => ['nullable', 'string'],
                'assigned_to_user_id' => ['required', 'exists:users,id'],
                'status' => ['nullable', 'string', 'in:pending,in_progress,completed,cancelled'],
                'priority' => ['nullable', 'string', 'in:low,medium,high'],
                'due_date' => ['nullable', 'date_format:Y-m-d H:i:s', 'after_or_equal:now'],
                'patient_id' => ['nullable', 'exists:patients,id'],
                'department_id' => ['nullable', 'exists:departments,id'],
            ]);

            $task = Task::create([
                'title' => $request->title,
                'description' => $request->description,
                'assigned_to_user_id' => $request->assigned_to_user_id,
                'assigned_by_user_id' => Auth::id(), // المستخدم المصادق عليه هو من يكلف المهمة
                'status' => $request->status ?? 'pending',
                'priority' => $request->priority ?? 'medium',
                'due_date' => $request->due_date,
                'patient_id' => $request->patient_id,
                'department_id' => $request->department_id,
            ]);

            return response()->json([
                'message' => 'تم إنشاء المهمة بنجاح.',
                'task' => $task->load('assignedTo.role', 'assignedBy.role', 'patient.user', 'department'),
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
     * @param  \App\Models\Task  $task
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Task $task)
    {
        // يمكن للمدير، المستخدم المكلف، أو المستخدم الذي كلف المهمة رؤية تفاصيلها
        $this->authorize('view', $task);

        return response()->json([
            'message' => 'تم جلب المهمة بنجاح.',
            'task' => $task->load('assignedTo.role', 'assignedBy.role', 'patient.user', 'department'),
            'status' => 'success'
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Task  $task
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, Task $task)
    {
        // يمكن للمدير، المستخدم المكلف، أو المستخدم الذي كلف المهمة تحديثها
        $this->authorize('update', $task);

        try {
            $request->validate([
                'title' => ['required', 'string', 'max:255'],
                'description' => ['nullable', 'string'],
                'assigned_to_user_id' => ['required', 'exists:users,id'],
                'status' => ['required', 'string', 'in:pending,in_progress,completed,cancelled'],
                'priority' => ['required', 'string', 'in:low,medium,high'],
                'due_date' => ['nullable', 'date_format:Y-m-d H:i:s', 'after_or_equal:now'],
                'patient_id' => ['nullable', 'exists:patients,id'],
                'department_id' => ['nullable', 'exists:departments,id'],
            ]);

            $task->update($request->all());

            return response()->json([
                'message' => 'تم تحديث المهمة بنجاح.',
                'task' => $task->load('assignedTo.role', 'assignedBy.role', 'patient.user', 'department'),
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
     * @param  \App\Models\Task  $task
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Task $task)
    {
        // يمكن للمدير فقط حذف المهام
        $this->authorize('delete', $task);

        try {
            $task->delete();

            return response()->json([
                'message' => 'تم حذف المهمة بنجاح.',
                'status' => 'success'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'حدث خطأ أثناء حذف المهمة: ' . $e->getMessage(),
                'status' => 'error'
            ], 500);
        }
    }
}
