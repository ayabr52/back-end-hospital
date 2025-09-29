<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Doctor;
use App\Models\User;
use App\Models\Department;
use App\Models\Role;
use Illuminate\Support\Facades\Hash; // لاستخدام Hash لتشفير كلمات المرور

class DoctorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // الحصول على دور الطبيب
        $doctorRole = Role::where('name', 'doctor')->first();
        if (!$doctorRole) {
            $this->command->error('Doctor role not found. Please run RoleSeeder first.');
            return;
        }

        // الحصول على بعض الأقسام الموجودة
        $surgeryDepartment = Department::where('name', 'قسم العمليات الجراحية')->first();
        $internalDepartment = Department::where('name', 'قسم الأطفال')->first();

        // إنشاء أو تحديث طبيب مرتبط بحساب المستخدم 'doctor@example.com'
        $doctorUser = User::where('email', 'doctor@example.com')->first();
        if ($doctorUser) {
            if (!Doctor::where('user_id', $doctorUser->id)->exists()) {
                Doctor::create([
                    'user_id' => $doctorUser->id,
                    'name' => 'د. أحمد',
                    'specialty' => 'جراحة',
                    'bio' => 'طبيب جراح ذو خبرة واسعة في العمليات المعقدة.',
                    'image' => 'https://placehold.co/200x200/CCCCCC/FFFFFF?text=Doctor+Ahmed',
                    'department_id' => $surgeryDepartment ? $surgeryDepartment->id : null,
                ]);
            }
        } else {
            // إذا لم يكن المستخدم موجودًا، قم بإنشائه أولاً
            $doctorUser = User::create([
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
            Doctor::create([
                'user_id' => $doctorUser->id,
                'name' => 'د. أحمد',
                'specialty' => 'جراحة',
                'bio' => 'طبيب جراح ذو خبرة واسعة في العمليات المعقدة.',
                'image' => 'https://placehold.co/200x200/CCCCCC/FFFFFF?text=Doctor+Ahmed',
                'department_id' => $surgeryDepartment ? $surgeryDepartment->id : null,
            ]);
        }


        // إنشاء مستخدمين وأطباء إضافيين
        $doctorsData = [
            [
                'user_email' => 'doctor.lama@example.com',
                'user_name' => 'د. لما قيسون',
                'user_phone' => '0912345610',
                'user_national_id' => '12345678910',
                'user_address' => 'دمشق',
                'user_dob' => '1988-06-25',
                'user_gender' => 'female',
                'specialty' => 'جراحة',
                'bio' => 'متخصصة في جراحة الأطفال والجراحة العامة.',
                'image' => 'https://placehold.co/200x200/CCCCCC/FFFFFF?text=Doctor+Lama',
                'department_name' => 'قسم العمليات الجراحية',
            ],
            [
                'user_email' => 'doctor.agheed@example.com',
                'user_name' => 'د. أغيد السلام',
                'user_phone' => '0912345611',
                'user_national_id' => '12345678911',
                'user_address' => 'حلب',
                'user_dob' => '1979-09-12',
                'user_gender' => 'male',
                'specialty' => 'داخلية',
                'bio' => 'استشاري أمراض باطنية، متخصص في أمراض الجهاز الهضمي.',
                'image' => 'https://placehold.co/200x200/CCCCCC/FFFFFF?text=Doctor+Agheed',
                'department_name' => 'قسم الأطفال',
            ],
            // أضف المزيد من الأطباء هنا
        ];

        foreach ($doctorsData as $data) {
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
                    'role_id' => $doctorRole->id,
                ]
            );

            $department = Department::where('name', $data['department_name'])->first();

            if (!Doctor::where('user_id', $user->id)->exists()) {
                Doctor::create([
                    'user_id' => $user->id,
                    'name' => $data['user_name'],
                    'specialty' => $data['specialty'],
                    'bio' => $data['bio'],
                    'image' => $data['image'],
                    'department_id' => $department ? $department->id : null,
                ]);
            }
        }
    }
}
