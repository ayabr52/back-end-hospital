<?php

namespace App\Policies;

use App\Models\User;
use App\Models\LabTest;
use Illuminate\Auth\Access\Response;

class LabTestPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        // المدير، الطبيب، وموظف المختبر يمكنهم رؤية جميع التحاليل
        // المريض يمكنه رؤية تحاليله فقط (المنطق يتم في المتحكم)
        return $user->role->name === 'admin' || $user->role->name === 'doctor' || $user->role->name === 'lab_technician' || $user->role->name === 'patient';
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, LabTest $labTest): bool
    {
        // المدير يمكنه رؤية أي تحليل
        if ($user->role->name === 'admin') {
            return true;
        }

        // الطبيب يمكنه رؤية التحاليل التي طلبها أو تحاليل مرضاه
        if ($user->role->name === 'doctor' && $user->doctor) {
            // يمكن للطبيب رؤية التحاليل التي طلبها
            if ($user->doctor->id === $labTest->doctor_id) {
                return true;
            }
            // يمكن للطبيب رؤية التحاليل الخاصة بمرضاه (إذا كان المريض لديه مواعيد مع هذا الطبيب، أو كان هذا الطبيب هو طبيبه الأساسي)
            // هذا يتطلب منطقًا أكثر تعقيدًا أو علاقة مباشرة بين الطبيب والمريض.
            // لتبسيط الأمر حالياً، سنسمح للطبيب برؤية أي تحليل إذا كان دوره طبيب.
            return true; // مؤقتاً، يسمح للطبيب برؤية أي تحليل.
        }

        // موظف المختبر يمكنه رؤية التحاليل التي أدخلها
        if ($user->role->name === 'lab_technician' && $user->id === $labTest->performed_by_user_id) {
            return true;
        }

        // المريض يمكنه رؤية تحاليله الخاصة فقط
        if ($user->role->name === 'patient' && $user->patient && $user->patient->id === $labTest->patient_id) {
            return true;
        }

        return false; // غير مصرح له
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // يمكن للمدير والطبيب وموظف المختبر إنشاء تحاليل مخبرية
        return $user->role->name === 'admin' || $user->role->name === 'lab_technician' || $user->role->name === 'doctor';
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, LabTest $labTest): bool
    {
        // يمكن للمدير تحديث أي تحليل
        if ($user->role->name === 'admin') {
            return true;
        }

        // موظف المختبر الذي أدخل التحليل يمكنه تحديثه (مثلاً لتغيير الحالة أو إضافة النتائج)
        if ($user->role->name === 'lab_technician' && $user->id === $labTest->performed_by_user_id) {
            return true;
        }

        return false; // غير مصرح له
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, LabTest $labTest): bool
    {
        // يمكن للمدير فقط حذف التحاليل المخبرية
        return $user->role->name === 'admin';
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, LabTest $labTest): bool
    {
        return $user->role->name === 'admin';
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, LabTest $labTest): bool
    {
        return $user->role->name === 'admin';
    }
}
