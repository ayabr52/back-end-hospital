<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Invoice;
use Illuminate\Auth\Access\Response;

class InvoicePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        // المدير، المحاسب، موظف الاستقبال يمكنهم رؤية جميع الفواتير
        // المريض يمكنه رؤية فواتيره فقط (المنطق يتم في المتحكم)
        return $user->role->name === 'admin' || $user->role->name === 'accountant' || $user->role->name === 'receptionist' || $user->role->name === 'patient';
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Invoice $invoice): bool
    {
        // المدير، المحاسب، موظف الاستقبال يمكنهم رؤية أي فاتورة
        if ($user->role->name === 'admin' || $user->role->name === 'accountant' || $user->role->name === 'receptionist') {
            return true;
        }

        // المريض يمكنه رؤية فواتيره الخاصة فقط
        if ($user->role->name === 'patient' && $user->patient && $user->patient->id === $invoice->patient_id) {
            return true;
        }

        return false; // غير مصرح له
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // يمكن للمدير، المحاسب، موظف الاستقبال إنشاء فواتير
        return $user->role->name === 'admin' || $user->role->name === 'accountant' || $user->role->name === 'receptionist';
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Invoice $invoice): bool
    {
        // يمكن للمدير، المحاسب، موظف الاستقبال تحديث الفواتير
        return $user->role->name === 'admin' || $user->role->name === 'accountant' || $user->role->name === 'receptionist';
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Invoice $invoice): bool
    {
        // يمكن للمدير والمحاسب فقط حذف الفواتير
        return $user->role->name === 'admin' || $user->role->name === 'accountant';
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Invoice $invoice): bool
    {
        return $user->role->name === 'admin' || $user->role->name === 'accountant';
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Invoice $invoice): bool
    {
        return $user->role->name === 'admin' || $user->role->name === 'accountant';
    }
}