<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Patient;
use Illuminate\Auth\Access\Response;

class PatientPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        // يمكن للمدير وموظف الاستقبال والطبيب رؤية قائمة جميع المرضى
        return $user->role->name === 'admin' || $user->role->name === 'receptionist' || $user->role->name === 'doctor';
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Patient $patient): bool
    {
        // يمكن للمدير، موظف الاستقبال، أو المريض نفسه رؤية تفاصيله
        return $user->role->name === 'admin' ||
               $user->role->name === 'receptionist' ||
               $user->id === $patient->user_id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // يمكن للمدير وموظف الاستقبال إنشاء سجلات مرضى جديدة
        return $user->role->name === 'admin' || $user->role->name === 'receptionist';
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Patient $patient): bool
    {
        // يمكن للمدير تحديث أي مريض، أو يمكن للمريض نفسه تحديث ملفه الشخصي
        return $user->role->name === 'admin' || $user->id === $patient->user_id;
    }

    /**
     * Determine whether the user can delete the model.
     */
public function delete(User $user, Patient $patient)
{
    //   المدير و الممرض يمكنهم الحذف
    return $user->role->name === 'admin' || $user->role->name === 'nurse';
}


    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Patient $patient): bool
    {
        return $user->role->name === 'admin';
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Patient $patient): bool
    {
        return $user->role->name === 'admin';
    }
}
