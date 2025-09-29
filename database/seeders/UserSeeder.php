<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User; // تأكد من استيراد موديل User
use App\Models\Role; // تأكد من استيراد موديل Role
use Illuminate\Support\Facades\Hash; // لاستخدام Hash لتشفير كلمات المرور

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // الحصول على معرفات الأدوار
        $adminRole = Role::where('name', 'admin')->first();
        $patientRole = Role::where('name', 'patient')->first();
        $doctorRole = Role::where('name', 'doctor')->first();
        $nurseRole = Role::where('name', 'nurse')->first();
        $receptionistRole = Role::where('name', 'receptionist')->first();
        $accountantRole = Role::where('name', 'accountant')->first();
        $pharmacistRole = Role::where('name', 'pharmacist')->first();

        // إنشاء مستخدمين تجريبيين (تجنب التكرار بناءً على البريد الإلكتروني)
        if ($adminRole && !User::where('email', 'admin@example.com')->exists()) {
            User::create([
                'name' => 'مدير النظام',
                'email' => 'admin@example.com',
                'password' => Hash::make('password'),
                'phone' => '0912345600',
                'national_id' => '12345678901',
                'address' => 'حمص حواش',
                'dob' => '1980-01-01',
                'gender' => 'male',
                'role_id' => $adminRole->id,
            ]);
        }

        if ($patientRole && !User::where('email', 'patient@example.com')->exists()) {
            User::create([
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
        }

        if ($doctorRole && !User::where('email', 'doctor@example.com')->exists()) {
            User::create([
                'name' => 'د. أحمد',
                'email' => 'doctor@example.com',
                'password' => Hash::make('password'),
                'phone' => '0912345602',
                'national_id' => '12345678903',
                'address' => 'حمص حواش',
                'dob' => '1975-03-20',
                'gender' => 'male',
                'role_id' => $doctorRole->id,
            ]);
        }

        if ($nurseRole && !User::where('email', 'nurse@example.com')->exists()) {
            User::create([
                'name' => 'ممرضة سارة',
                'email' => 'nurse@example.com',
                'password' => Hash::make('password'),
                'phone' => '0912345603',
                'national_id' => '12345678904',
                'address' => 'حلب',
                'dob' => '1992-07-07',
                'gender' => 'female',
                'role_id' => $nurseRole->id,
            ]);
        }

        if ($receptionistRole && !User::where('email', 'reception@example.com')->exists()) {
            User::create([
                'name' => 'موظف استقبال',
                'email' => 'reception@example.com',
                'password' => Hash::make('password'),
                'phone' => '0912345604',
                'national_id' => '12345678905',
                'address' => 'حمص حواش',
                'dob' => '1988-09-01',
                'gender' => 'male',
                'role_id' => $receptionistRole->id,
            ]);
        }

        if ($accountantRole && !User::where('email', 'accountant@example.com')->exists()) {
            User::create([
                'name' => 'محاسب خالد',
                'email' => 'accountant@example.com',
                'password' => Hash::make('password'),
                'phone' => '0912345605',
                'national_id' => '12345678906',
                'address' => 'دمشق',
                'dob' => '1985-11-22',
                'gender' => 'male',
                'role_id' => $accountantRole->id,
            ]);
        }

        if ($pharmacistRole && !User::where('email', 'pharmacist@example.com')->exists()) {
            User::create([
                'name' => 'صيدلي علي',
                'email' => 'pharmacist@example.com',
                'password' => Hash::make('password'),
                'phone' => '0912345606',
                'national_id' => '12345678907',
                'address' => 'حلب',
                'dob' => '1983-04-10',
                'gender' => 'male',
                'role_id' => $pharmacistRole->id,
            ]);
        }
    }
}
