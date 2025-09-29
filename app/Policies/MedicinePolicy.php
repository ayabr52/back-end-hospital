<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Medicine;
use Illuminate\Auth\Access\Response;

class MedicinePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        // المدير، المحاسب، موظف الاستقبال، والصيدلي يمكنهم رؤية جميع الأدوية
        return $user->role->name === 'admin' || $user->role->name === 'accountant' || $user->role->name === 'receptionist' || $user->role->name === 'pharmacist';
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Medicine $medicine): bool
    {
        // المدير، المحاسب، موظف الاستقبال، والصيدلي يمكنهم رؤية تفاصيل أي دواء
        return $user->role->name === 'admin' || $user->role->name === 'accountant' || $user->role->name === 'receptionist' || $user->role->name === 'pharmacist';
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // يمكن للمدير والصيدلي فقط إضافة أدوية جديدة
        return $user->role->name === 'admin' || $user->role->name === 'pharmacist';
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Medicine $medicine): bool
    {
        // يمكن للمدير والصيدلي فقط تحديث الأدوية
        return $user->role->name === 'admin' || $user->role->name === 'pharmacist';
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Medicine $medicine): bool
    {
        // يمكن للمدير والصيدلي فقط حذف الأدوية
        return $user->role->name === 'admin' || $user->role->name === 'pharmacist';
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Medicine $medicine): bool
    {
        return $user->role->name === 'admin' || $user->role->name === 'pharmacist';
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Medicine $medicine): bool
    {
        return $user->role->name === 'admin' || $user->role->name === 'pharmacist';
    }
}
