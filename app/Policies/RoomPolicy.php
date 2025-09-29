<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Room;
use Illuminate\Auth\Access\Response;

class RoomPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        // يمكن للمدير وموظف الاستقبال رؤية جميع الغرف
        return $user->role->name === 'admin' || $user->role->name === 'receptionist';
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Room $room): bool
    {
        // يمكن للمدير وموظف الاستقبال رؤية تفاصيل أي غرفة
        return $user->role->name === 'admin' || $user->role->name === 'receptionist';
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // يمكن للمدير فقط إنشاء غرف جديدة
        return $user->role->name === 'admin';
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Room $room): bool
    {
        // يمكن للمدير وموظف الاستقبال تحديث الغرف
        return $user->role->name === 'admin' || $user->role->name === 'receptionist';
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Room $room): bool
    {
        // يمكن للمدير فقط حذف الغرف
        return $user->role->name === 'admin';
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Room $room): bool
    {
        return $user->role->name === 'admin';
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Room $room): bool
    {
        return $user->role->name === 'admin';
    }
}