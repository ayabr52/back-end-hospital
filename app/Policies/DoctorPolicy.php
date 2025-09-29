<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Doctor;
use Illuminate\Auth\Access\Response;

class DoctorPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        // يمكن لأي مستخدم مصادق عليه (أو حتى غير مصادق عليه إذا أردت) رؤية الأطباء
        // بما أننا سمحنا بذلك في المتحكم، يمكن ترك هذه فارغة أو إرجاع true
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Doctor $doctor): bool
    {
        // يمكن لأي مستخدم مصادق عليه (أو حتى غير مصادق عليه إذا أردت) رؤية طبيب معين
        return true;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // يمكن للمدير فقط إنشاء سجلات أطباء جديدة
        return $user->role->name === 'admin';
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Doctor $doctor): bool
    {
        // يمكن للمدير تحديث أي طبيب، أو يمكن للطبيب نفسه تحديث ملفه الشخصي
        return $user->role->name === 'admin' || $user->id === $doctor->user_id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Doctor $doctor): bool
    {
        // يمكن للمدير فقط حذف الأطباء
        return $user->role->name === 'admin';
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Doctor $doctor): bool
    {
        return $user->role->name === 'admin';
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Doctor $doctor): bool
    {
        return $user->role->name === 'admin';
    }
}
