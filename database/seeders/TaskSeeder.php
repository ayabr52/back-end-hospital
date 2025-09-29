<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Task;
use App\Models\User;
use App\Models\Patient;
use App\Models\Department;
use Carbon\Carbon;

class TaskSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // الحصول على بعض المستخدمين والمرضى والأقسام الموجودة
        $adminUser = User::where('email', 'admin@example.com')->first();
        $doctorUser = User::where('email', 'doctor@example.com')->first();
        $nurseUser = User::where('email', 'nurse@example.com')->first();
        $receptionistUser = User::where('email', 'reception@example.com')->first();
        $patientUser = User::where('email', 'patient@example.com')->first();

        // **التغيير هنا: جلب موديل المريض مباشرة بعد التأكد من وجود المستخدم**
        $patient1 = $patientUser ? Patient::where('user_id', $patientUser->id)->first() : null;
        $icuDepartment = Department::where('name', 'قسم العناية المركزة')->first();

        if (!$adminUser || !$doctorUser || !$nurseUser || !$receptionistUser || !$patient1 || !$icuDepartment) {
            $this->command->error('Required users, patient, or department not found. Please run previous Seeders first.');
            return;
        }

        $tasksData = [
            [
                'title' => 'مراجعة سجل المريض',
                'description' => 'مراجعة السجل الطبي للمريض ' . $patient1->name . ' قبل الموعد.',
                'assigned_to_user_id' => $doctorUser->id,
                'assigned_by_user_id' => $adminUser->id,
                'status' => 'pending',
                'priority' => 'high',
                'due_date' => Carbon::now()->addHours(2),
                'patient_id' => $patient1->id,
                'department_id' => null,
            ],
            [
                'title' => 'تنظيف الغرفة 102',
                'description' => 'تنظيف وتعقيم الغرفة رقم 102 بعد خروج المريض.',
                'assigned_to_user_id' => $nurseUser->id,
                'assigned_by_user_id' => $receptionistUser->id,
                'status' => 'in_progress',
                'priority' => 'medium',
                'due_date' => Carbon::now()->addHours(4),
                'patient_id' => null,
                'department_id' => $icuDepartment->id,
            ],
            [
                'title' => 'إعداد تقرير شهري',
                'description' => 'إعداد تقرير الأداء الشهري لقسم العناية المركزة.',
                'assigned_to_user_id' => $adminUser->id,
                'assigned_by_user_id' => $adminUser->id,
                'status' => 'pending',
                'priority' => 'low',
                'due_date' => Carbon::now()->addDays(5),
                'patient_id' => null,
                'department_id' => $icuDepartment->id,
            ],
            [
                'title' => 'تأكيد موعد المريض',
                'description' => 'الاتصال بالمريض ' . $patient1->name . ' لتأكيد موعده غداً.',
                'assigned_to_user_id' => $receptionistUser->id,
                'assigned_by_user_id' => $doctorUser->id,
                'status' => 'pending',
                'priority' => 'high',
                'due_date' => Carbon::now()->addHours(1),
                'patient_id' => $patient1->id,
                'department_id' => null,
            ],
        ];

        foreach ($tasksData as $data) {
            // التحقق من وجود المهمة بناءً على العنوان والمكلف به ومن كلفها وتاريخ الاستحقاق
            if (!Task::where('title', $data['title'])
                     ->where('assigned_to_user_id', $data['assigned_to_user_id'])
                     ->where('assigned_by_user_id', $data['assigned_by_user_id'])
                     ->where('due_date', $data['due_date'])
                     ->exists()) {
                Task::create($data);
            }
        }
    }
}
