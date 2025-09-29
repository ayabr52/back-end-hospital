<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Task;
use Illuminate\Auth\Access\Response;

class TaskPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        // جميع الأدوار المحددة يمكنها رؤية المهام ذات الصلة
        return in_array($user->role->name, ['admin', 'doctor', 'nurse', 'receptionist']);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Task $task): bool
    {
        // المدير يمكنه رؤية أي مهمة
        if ($user->role->name === 'admin') {
            return true;
        }

        // المستخدم المكلف بالمهمة يمكنه رؤيتها
        if ($user->id === $task->assigned_to_user_id) {
            return true;
        }

        // المستخدم الذي كلف المهمة يمكنه رؤيتها
        if ($user->id === $task->assigned_by_user_id) {
            return true;
        }

        return false; // غير مصرح له
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // يمكن للمدير، الطبيب، موظف الاستقبال إنشاء مهام
        return in_array($user->role->name, ['admin', 'doctor', 'receptionist']);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Task $task): bool
    {
        // المدير يمكنه تحديث أي مهمة
        if ($user->role->name === 'admin') {
            return true;
        }

        // المستخدم المكلف بالمهمة يمكنه تحديثها (مثلاً تغيير الحالة)
        if ($user->id === $task->assigned_to_user_id) {
            return true;
        }

        // المستخدم الذي كلف المهمة يمكنه تحديثها
        if ($user->id === $task->assigned_by_user_id) {
            return true;
        }

        return false; // غير مصرح له
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Task $task): bool
    {
        // يمكن للمدير فقط حذف المهام
        return $user->role->name === 'admin';
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Task $task): bool
    {
        return $user->role->name === 'admin';
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Task $task): bool
    {
        return $user->role->name === 'admin';
    }
}
