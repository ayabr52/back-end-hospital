<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Prescription;
use Illuminate\Auth\Access\Response;

class PrescriptionPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        // المدير والطبيب والصيدلي يمكنهم رؤية جميع الوصفات
        // المريض يمكنه رؤية وصفاته فقط (المنطق يتم في المتحكم)
        return $user->role->name === 'admin' || $user->role->name === 'doctor' || $user->role->name === 'pharmacist' || $user->role->name === 'patient';
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Prescription $prescription): bool
    {
        // المدير يمكنه رؤية أي وصفة
        if ($user->role->name === 'admin') {
            return true;
        }

        // الطبيب يمكنه رؤية الوصفات التي كتبها
        if ($user->role->name === 'doctor' && $user->doctor && $user->doctor->id === $prescription->doctor_id) {
            return true;
        }

        // الصيدلي يمكنه رؤية أي وصفة
        if ($user->role->name === 'pharmacist') {
            return true;
        }

        // المريض يمكنه رؤية وصفاته الخاصة فقط
        if ($user->role->name === 'patient' && $user->patient && $user->patient->id === $prescription->patient_id) {
            return true;
        }

        return false; // غير مصرح له
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // يمكن للمدير والطبيب و الصيدلي إنشاء وصفات أدوية
        return $user->role->name === 'admin' || $user->role->name === 'doctor'|| $user->role->name === 'pharmacist';
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Prescription $prescription): bool
    {
        // يمكن للمدير تحديث أي وصفة
        if ($user->role->name === 'admin') {
            return true;
        }

        // الطبيب الذي كتب الوصفة يمكنه تحديثها
        if ($user->role->name === 'doctor' && $user->doctor && $user->doctor->id === $prescription->doctor_id) {
            return true;
        }

        return false; // غير مصرح له
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Prescription $prescription): bool
    {
        // يمكن للمدير فقط حذف وصفات الأدوية
        return $user->role->name === 'admin';
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Prescription $prescription): bool
    {
        return $user->role->name === 'admin';
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Prescription $prescription): bool
    {
        return $user->role->name === 'admin';
    }
}
