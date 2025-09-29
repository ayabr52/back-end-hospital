<?php

namespace App\Providers;

use App\Models\Doctor;
use App\Policies\DoctorPolicy;
use App\Models\Patient;
use App\Policies\PatientPolicy;
use App\Models\Appointment;
use App\Policies\AppointmentPolicy;
use App\Models\Room; // استيراد موديل Room
use App\Policies\RoomPolicy; // استيراد RoomPolicy
use App\Models\Invoice;
use App\Policies\InvoicePolicy;
use App\Models\Medicine;
use App\Policies\MedicinePolicy;
use App\Models\Task;
use App\Policies\TaskPolicy; // تم تصحيح الخطأ هنا
use App\Models\MedicalRecord;
use App\Policies\MedicalRecordPolicy; // تم تصحيح الخطأ هنا
use App\Models\LabTest;
use App\Policies\LabTestPolicy; // تم تصحيح الخطأ هنا
use App\Models\Prescription; // استيراد موديل Prescription
use App\Policies\PrescriptionPolicy; // استيراد PrescriptionPolicy
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Doctor::class => DoctorPolicy::class,
        Patient::class => PatientPolicy::class,
        Appointment::class => AppointmentPolicy::class,
        Room::class => RoomPolicy::class, // إضافة جديدة هنا
        Invoice::class => InvoicePolicy::class,
        Medicine::class => MedicinePolicy::class,
        Task::class => TaskPolicy::class,
        MedicalRecord::class => MedicalRecordPolicy::class,
        LabTest::class => LabTestPolicy::class,
        Prescription::class => PrescriptionPolicy::class, // إضافة جديدة هنا
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        //
    }
}
