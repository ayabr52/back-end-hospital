<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\MedicalRecord;
use App\Models\Patient;
use App\Models\Doctor;
use App\Models\User; // لاستخدام User لجلب الطبيب والمريض
use Carbon\Carbon;

class MedicalRecordSeeder extends Seeder
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

        if (!$patient1 || !$patient2 || !$doctor1 || !$doctor2) {
            $this->command->error('Required patients or doctors not found for MedicalRecordSeeder. Please run previous Seeders first.');
            return;
        }

        $medicalRecordsData = [
            [
                'patient_id' => $patient1->id,
                'doctor_id' => $doctor1->id,
                'record_date' => Carbon::now()->subDays(30)->format('Y-m-d'),
                'diagnosis' => 'نزلة برد حادة',
                'treatment' => 'راحة تامة، سوائل دافئة، باراسيتامول 500 ملغ مرتين يومياً.',
                'notes' => 'المريض استجاب بشكل جيد للعلاج.',
            ],
            [
                'patient_id' => $patient2->id,
                'doctor_id' => $doctor2->id,
                'record_date' => Carbon::now()->subDays(15)->format('Y-m-d'),
                'diagnosis' => 'التهاب لوزتين حاد',
                'treatment' => 'مضاد حيوي (أموكسيسيلين) لمدة 7 أيام، غرغرة مطهرة.',
                'notes' => 'ينصح بمراجعة الطبيب بعد أسبوع لتقييم الحالة.',
            ],
            [
                'patient_id' => $patient1->id,
                'doctor_id' => $doctor1->id,
                'record_date' => Carbon::now()->subDays(5)->format('Y-m-d'),
                'diagnosis' => 'فحص روتيني',
                'treatment' => 'لا يوجد علاج محدد، ينصح بالحفاظ على نمط حياة صحي.',
                'notes' => 'جميع المؤشرات الحيوية ضمن المعدل الطبيعي.',
            ],
        ];

        foreach ($medicalRecordsData as $data) {
            // التحقق من وجود السجل لتجنب التكرار بناءً على المريض والطبيب والتاريخ والتشخيص
            if (!MedicalRecord::where('patient_id', $data['patient_id'])
                             ->where('doctor_id', $data['doctor_id'])
                             ->where('record_date', $data['record_date'])
                             ->where('diagnosis', $data['diagnosis'])
                             ->exists()) {
                MedicalRecord::create($data);
            }
        }
    }
}
