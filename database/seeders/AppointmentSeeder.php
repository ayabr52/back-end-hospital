<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Appointment;
use App\Models\Patient;
use App\Models\Doctor;
use Carbon\Carbon; // لاستخدام التواريخ والأوقات بسهولة

class AppointmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // الحصول على بعض المرضى والأطباء الموجودين
        $patient1 = Patient::whereHas('user', function ($query) {
            $query->where('email', 'patient@example.com');
        })->first();
        $patient2 = Patient::whereHas('user', function ($query) {
            $query->where('email', 'patient.ali@example.com');
        })->first();

        $doctor1 = Doctor::whereHas('user', function ($query) {
            $query->where('email', 'doctor@example.com');
        })->first();
        $doctor2 = Doctor::whereHas('user', function ($query) {
            $query->where('email', 'doctor.lama@example.com');
        })->first();

        if (!$patient1 || !$doctor1 || !$patient2 || !$doctor2) {
            $this->command->error('Patients or Doctors not found. Please run UserSeeder and DoctorSeeder first.');
            return;
        }

        // أمثلة لمواعيد
        if (!Appointment::where('patient_id', $patient1->id)->where('doctor_id', $doctor1->id)->where('appointment_date', Carbon::now()->addDays(2)->format('Y-m-d 10:00:00'))->exists()) {
            Appointment::create([
                'patient_id' => $patient1->id,
                'doctor_id' => $doctor1->id,
                'appointment_date' => Carbon::now()->addDays(2)->format('Y-m-d 10:00:00'), // موعد بعد يومين
                'status' => 'confirmed',
                'notes' => 'فحص روتيني',
            ]);
        }

        if (!Appointment::where('patient_id', $patient2->id)->where('doctor_id', $doctor1->id)->where('appointment_date', Carbon::now()->addDays(3)->format('Y-m-d 11:30:00'))->exists()) {
            Appointment::create([
                'patient_id' => $patient2->id,
                'doctor_id' => $doctor1->id,
                'appointment_date' => Carbon::now()->addDays(3)->format('Y-m-d 11:30:00'), // موعد بعد 3 أيام
                'status' => 'pending',
                'notes' => 'استشارة أولية',
            ]);
        }

        if (!Appointment::where('patient_id', $patient1->id)->where('doctor_id', $doctor2->id)->where('appointment_date', Carbon::now()->addDays(4)->format('Y-m-d 09:00:00'))->exists()) {
            Appointment::create([
                'patient_id' => $patient1->id,
                'doctor_id' => $doctor2->id,
                'appointment_date' => Carbon::now()->addDays(4)->format('Y-m-d 09:00:00'), // موعد بعد 4 أيام
                'status' => 'confirmed',
                'notes' => 'فحص متابعة',
            ]);
        }
    }
}
