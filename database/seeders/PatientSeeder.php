<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Patient;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;

class PatientSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $patientRole = Role::where('name', 'patient')->first();
        if (!$patientRole) {
            $this->command->error('Patient role not found. Please run RoleSeeder first.');
            return;
        }

        // إنشاء أو تحديث مريض مرتبط بحساب المستخدم 'patient@example.com'
        $patientUser = User::where('email', 'patient@example.com')->first();
        if ($patientUser) {
            if (!Patient::where('user_id', $patientUser->id)->exists()) {
                Patient::create([
                    'user_id' => $patientUser->id,
                    'name' => 'مريض تجريبي',
                    'phone' => '0912345601',
                    'national_id' => '12345678902',
                    'address' => 'دمشق',
                    'dob' => '1990-05-15',
                    'gender' => 'female',
                ]);
            }
        } else {
            // إذا لم يكن المستخدم موجودًا، قم بإنشائه أولاً
            $patientUser = User::create([
                'name' => 'مريض تجريبي',
                'email' => 'patient@example.com',
                'password' => Hash::make('password'),
                'phone' => '0912345601',
                'national_id' => '12345678902',
                'address' => 'دمشق',
                'dob' => '1990-05-15',
                'gender' => 'female',
                'role_id' => $patientRole->id,
            ]);
            Patient::create([
                'user_id' => $patientUser->id,
                'name' => 'مريض تجريبي',
                'phone' => '0912345601',
                'national_id' => '12345678902',
                'address' => 'دمشق',
                'dob' => '1990-05-15',
                'gender' => 'female',
            ]);
        }

        // أمثلة لمرضى إضافيين
        $patientsData = [
            [
                'user_email' => 'patient.ali@example.com',
                'user_name' => 'علياء محمود',
                'user_phone' => '0912345620',
                'user_national_id' => '12345678920',
                'user_address' => 'حمص',
                'user_dob' => '1985-01-20',
                'user_gender' => 'female',
            ],
            [
                'user_email' => 'patient.sami@example.com',
                'user_name' => 'سامي خالد',
                'user_phone' => '0912345621',
                'user_national_id' => '12345678921',
                'user_address' => 'حلب',
                'user_dob' => '1992-07-07',
                'user_gender' => 'male',
            ],
        ];

        foreach ($patientsData as $data) {
            $user = User::firstOrCreate(
                ['email' => $data['user_email']],
                [
                    'name' => $data['user_name'],
                    'password' => Hash::make('password'), // كلمة مرور افتراضية
                    'phone' => $data['user_phone'],
                    'national_id' => $data['user_national_id'],
                    'address' => $data['user_address'],
                    'dob' => $data['user_dob'],
                    'gender' => $data['user_gender'],
                    'role_id' => $patientRole->id,
                ]
            );

            if (!Patient::where('user_id', $user->id)->exists()) {
                Patient::create([
                    'user_id' => $user->id,
                    'name' => $data['user_name'],
                    'phone' => $data['user_phone'],
                    'national_id' => $data['user_national_id'],
                    'address' => $data['user_address'],
                    'dob' => $data['user_dob'],
                    'gender' => $data['user_gender'],
                ]);
            }
        }
    }
}
