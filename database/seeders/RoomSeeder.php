<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Room;
use App\Models\Department;

class RoomSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // الحصول على بعض الأقسام الموجودة
        $icuDepartment = Department::where('name', 'قسم العناية المركزة')->first();
        $surgeryDepartment = Department::where('name', 'قسم العمليات الجراحية')->first();
        $pediatricsDepartment = Department::where('name', 'قسم الأطفال')->first(); // إذا كان موجوداً

        if (!$icuDepartment || !$surgeryDepartment) {
            $this->command->error('Departments (ICU or Surgery) not found. Please run DepartmentSeeder first.');
            return;
        }

        $roomsData = [
            [
                'room_number' => '101',
                'type' => 'private',
                'capacity' => 1,
                'status' => 'available',
                'notes' => 'غرفة خاصة مع حمام داخلي.',
                'department_id' => $pediatricsDepartment ? $pediatricsDepartment->id : null,
            ],
            [
                'room_number' => '102',
                'type' => 'semi-private',
                'capacity' => 2,
                'status' => 'occupied',
                'notes' => 'غرفة شبه خاصة، سرير واحد مشغول.',
                'department_id' => $pediatricsDepartment ? $pediatricsDepartment->id : null,
            ],
            [
                'room_number' => 'ICU-1',
                'type' => 'ICU',
                'capacity' => 1,
                'status' => 'available',
                'notes' => 'وحدة عناية مركزة مجهزة بالكامل.',
                'department_id' => $icuDepartment->id,
            ],
            [
                'room_number' => 'OR-1',
                'type' => 'OR',
                'capacity' => 0, // غرف العمليات عادة لا تحتوي على أسرة للمرضى المقيمين
                'status' => 'available',
                'notes' => 'غرفة عمليات رئيسية.',
                'department_id' => $surgeryDepartment->id,
            ],
            [
                'room_number' => '201',
                'type' => 'ward',
                'capacity' => 4,
                'status' => 'available',
                'notes' => 'جناح عام للرجال.',
                'department_id' => null, // يمكن أن تكون غير مرتبطة بقسم معين بشكل مباشر
            ],
        ];

        foreach ($roomsData as $data) {
            if (!Room::where('room_number', $data['room_number'])->exists()) {
                Room::create($data);
            }
        }
    }
}
