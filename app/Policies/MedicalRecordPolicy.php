<?php

namespace App\Policies;

use App\Models\User;
use App\Models\MedicalRecord;
use Illuminate\Auth\Access\Response;

class MedicalRecordPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        // المدير والطبيب يمكنهم رؤية جميع السجلات الطبية
        // المريض يمكنه رؤية سجلاته فقط (المنطق يتم في المتحكم)
        return $user->role->name === 'admin' || $user->role->name === 'doctor' || $user->role->name === 'patient';
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, MedicalRecord $medicalRecord): bool
    {
        // المدير يمكنه رؤية أي سجل طبي
        if ($user->role->name === 'admin') {
            return true;
        }

        // الطبيب يمكنه رؤية السجلات التي أنشأها أو السجلات الخاصة بمرضاه
        if ($user->role->name === 'doctor' && $user->doctor) {
            // يمكن للطبيب رؤية السجلات التي أنشأها
            if ($user->doctor->id === $medicalRecord->doctor_id) {
                return true;
            }
            // يمكن للطبيب رؤية السجلات الخاصة بمرضاه (إذا كان المريض لديه مواعيد مع هذا الطبيب، أو كان هذا الطبيب هو طبيبه الأساسي)
            // هذا يتطلب منطقًا أكثر تعقيدًا أو علاقة مباشرة بين الطبيب والمريض.
            // لتبسيط الأمر حالياً، سنسمح للطبيب برؤية أي سجل إذا كان دوره طبيب.
            // إذا أردت تقييدها أكثر، ستحتاج إلى إضافة علاقات أو منطق تحقق إضافي.
            return true; // مؤقتاً، يسمح للطبيب برؤية أي سجل.
        }

        // المريض يمكنه رؤية سجلاته الخاصة فقط
        if ($user->role->name === 'patient' && $user->patient && $user->patient->id === $medicalRecord->patient_id) {
            return true;
        }

        return false; // غير مصرح له
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // يمكن للمدير والطبيب فقط إنشاء سجلات طبية + الصيدلي
        return $user->role->name === 'admin' || $user->role->name === 'doctor' ||$user->role->name==='pharmacist';
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, MedicalRecord $medicalRecord): bool
    {
        // يمكن للمدير تحديث أي سجل طبي
        if ($user->role->name === 'admin') {
            return true;
        }

        // الطبيب الذي أنشأ السجل يمكنه تحديثه
        if ($user->role->name === 'doctor' && $user->doctor && $user->doctor->id === $medicalRecord->doctor_id) {
            return true;
        }

        return false; // غير مصرح له
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, MedicalRecord $medicalRecord): bool
    {
        // يمكن للمدير حذف أي سجل طبي
        if ($user->role->name === 'admin') {
            return true;
        }

        // الطبيب الذي أنشأ السجل يمكنه حذفه
        if ($user->role->name === 'doctor' && $user->doctor) {
            return $user->doctor->id === $medicalRecord->doctor_id;
        }

        return false; // غير مصرح له
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, MedicalRecord $medicalRecord): bool
    {
        return $user->role->name === 'admin';
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, MedicalRecord $medicalRecord): bool
    {
        return $user->role->name === 'admin';
    }
}
