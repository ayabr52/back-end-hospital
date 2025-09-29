<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Prescription;
use App\Models\Patient;
use App\Models\Doctor;
use App\Models\Medicine;
use App\Models\User; // لاستخدام User لجلب الطبيب والمريض
use Carbon\Carbon;

class PrescriptionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // الحصول على بعض المرضى والأطباء والأدوية الموجودة
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

        $paracetamol = Medicine::where('name', 'Paracetamol 500mg')->first();
        $amoxicillin = Medicine::where('name', 'Amoxicillin 250mg/5ml')->first();
        $ibuprofen = Medicine::where('name', 'Ibuprofen 400mg')->first();

        if (!$patient1 || !$patient2 || !$doctor1 || !$doctor2 || !$paracetamol || !$amoxicillin || !$ibuprofen) {
            $this->command->error('Required patients, doctors, or medicines not found for PrescriptionSeeder. Please run previous Seeders first.');
            return;
        }

        $prescriptionsData = [
            [
                'patient_id' => $patient1->id,
                'doctor_id' => $doctor1->id,
                'prescription_date' => Carbon::now()->subDays(5)->format('Y-m-d'),
                'notes' => 'وصفة لعلاج نزلات البرد.',
                'medicines' => [
                    [
                        'medicine_id' => $paracetamol->id,
                        'dosage' => '500mg',
                        'frequency' => 'مرتين يومياً',
                        'duration' => '5 أيام',
                        'instructions' => 'مع الطعام.',
                    ],
                    [
                        'medicine_id' => $ibuprofen->id,
                        'dosage' => '400mg',
                        'frequency' => 'مرة واحدة يومياً',
                        'duration' => '3 أيام',
                        'instructions' => 'عند الحاجة للألم.',
                    ],
                ],
            ],
            [
                'patient_id' => $patient2->id,
                'doctor_id' => $doctor2->id,
                'prescription_date' => Carbon::now()->subDays(2)->format('Y-m-d'),
                'notes' => 'وصفة لعلاج التهاب بكتيري.',
                'medicines' => [
                    [
                        'medicine_id' => $amoxicillin->id,
                        'dosage' => '250mg/5ml',
                        'frequency' => '3 مرات يومياً',
                        'duration' => '7 أيام',
                        'instructions' => 'قبل الطعام بنصف ساعة.',
                    ],
                ],
            ],
        ];

        foreach ($prescriptionsData as $data) {
            // التحقق من وجود الوصفة لتجنب التكرار
            $existingPrescription = Prescription::where('patient_id', $data['patient_id'])
                                                ->where('doctor_id', $data['doctor_id'])
                                                ->where('prescription_date', $data['prescription_date'])
                                                ->first();

            if (!$existingPrescription) {
                $prescription = Prescription::create([
                    'patient_id' => $data['patient_id'],
                    'doctor_id' => $data['doctor_id'],
                    'prescription_date' => $data['prescription_date'],
                    'notes' => $data['notes'],
                ]);

                $medicinesToAttach = [];
                foreach ($data['medicines'] as $med) {
                    $medicinesToAttach[$med['medicine_id']] = [
                        'dosage' => $med['dosage'],
                        'frequency' => $med['frequency'],
                        'duration' => $med['duration'],
                        'instructions' => $med['instructions'],
                    ];
                }
                $prescription->medicines()->attach($medicinesToAttach);
            }
        }
    }
}
