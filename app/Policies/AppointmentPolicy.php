<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Appointment;
use Illuminate\Auth\Access\Response;

class AppointmentPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        // جميع الأدوار المصادق عليها يمكنها رؤية المواعيد الخاصة بها أو جميع المواعيد حسب الدور
        return true; // المنطق التفصيلي تم التعامل معه في المتحكم
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Appointment $appointment): bool
    {
        // المدير وموظف الاستقبال يمكنهم رؤية أي موعد
        if ($user->role->name === 'admin' || $user->role->name === 'receptionist') {
            return true;
        }

        // الطبيب يمكنه رؤية المواعيد المرتبطة به
        if ($user->role->name === 'doctor' && $user->doctor && $user->doctor->id === $appointment->doctor_id) {
            return true;
        }

        // المريض يمكنه رؤية المواعيد المرتبطة به
        if ($user->role->name === 'patient' && $user->patient && $user->patient->id === $appointment->patient_id) {
            return true;
        }

        return false; // غير مصرح له
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // المدير وموظف الاستقبال يمكنهم إنشاء أي موعد
        // المريض يمكنه إنشاء موعد لنفسه (التحقق من patient_id يتم في المتحكم)
        return $user->role->name === 'admin' || $user->role->name === 'receptionist' || $user->role->name === 'patient';
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Appointment $appointment): bool
    {
        // المدير وموظف الاستقبال يمكنهم تحديث أي موعد
        if ($user->role->name === 'admin' || $user->role->name === 'receptionist') {
            return true;
        }

        // الطبيب يمكنه تحديث مواعيده (مثلاً لتغيير الحالة)
        if ($user->role->name === 'doctor' && $user->doctor && $user->doctor->id === $appointment->doctor_id) {
            return true;
        }

        // المريض لا يمكنه تحديث الموعد بعد إنشائه (يمكن تعديل هذا المنطق حسب الحاجة)
        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Appointment $appointment): bool
    {
        // المدير وموظف الاستقبال يمكنهم حذف أي موعد
        return $user->role->name === 'admin' || $user->role->name === 'receptionist';
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Appointment $appointment): bool
    {
        return $user->role->name === 'admin';
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Appointment $appointment): bool
    {
        return $user->role->name === 'admin';
    }
}
