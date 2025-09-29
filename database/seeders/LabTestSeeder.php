<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\LabTest;
use App\Models\Patient;
use App\Models\Doctor;
use App\Models\User;
use Carbon\Carbon;

class LabTestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // الحصول على بعض المرضى والأطباء والمستخدمين (المدير/الطبيب كمنفذ)
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

        $adminUser = User::where('email', 'admin@example.com')->first();


        if (!$patient1 || !$patient2 || !$doctor1 || !$doctor2 || !$adminUser) {
            $this->command->error('Required patients, doctors, or admin user not found for LabTestSeeder. Please run previous Seeders first.');
            return;
        }

        $labTestsData = [
            [
                'patient_id' => $patient1->id,
                'doctor_id' => $doctor1->id,
                // تعيين performed_by_user_id إلى معرف الطبيب الذي طلب التحليل أو المدير
                'performed_by_user_id' => $doctor1->user->id,
                'test_type' => 'CBC (Complete Blood Count)',
                'test_date' => Carbon::now()->subDays(7)->format('Y-m-d'),
                'result_status' => 'completed',
                'results' => json_encode([
                    'WBC' => '8.5 K/uL',
                    'RBC' => '4.8 M/uL',
                    'Hemoglobin' => '14.2 g/dL',
                    'Platelets' => '250 K/uL'
                ]),
                'notes' => 'نتائج طبيعية.',
            ],
            [
                'patient_id' => $patient2->id,
                'doctor_id' => $doctor2->id,
                // تعيين performed_by_user_id إلى معرف الطبيب الذي طلب التحليل أو المدير
                'performed_by_user_id' => $doctor2->user->id,
                'test_type' => 'Urine Analysis',
                'test_date' => Carbon::now()->subDays(3)->format('Y-m-d'),
                'result_status' => 'reviewed',
                'results' => json_encode([
                    'Color' => 'Yellow',
                    'Clarity' => 'Clear',
                    'Glucose' => 'Negative',
                    'Protein' => 'Negative',
                    'WBC' => '2-4 /HPF'
                ]),
                'notes' => 'لا توجد مؤشرات على التهاب المسالك البولية.',
            ],
            [
                'patient_id' => $patient1->id,
                'doctor_id' => $doctor1->id,
                // تعيين performed_by_user_id إلى معرف الطبيب الذي طلب التحليل أو المدير
                'performed_by_user_id' => $adminUser->id, // يمكن للمدير أيضاً إدخال نتائج
                'test_type' => 'Blood Glucose',
                'test_date' => Carbon::now()->subDays(1)->format('Y-m-d'),
                'result_status' => 'pending',
                'results' => null, // لم يتم إدخال النتائج بعد
                'notes' => 'تحليل سكر صائم.',
            ],
        ];

        foreach ($labTestsData as $data) {
            // التحقق من وجود التحليل لتجنب التكرار بناءً على المريض ونوع التحليل والتاريخ
            if (!LabTest::where('patient_id', $data['patient_id'])
                        ->where('test_type', $data['test_type'])
                        ->where('test_date', $data['test_date'])
                        ->exists()) {
                LabTest::create($data);
            }
        }
    }
}
