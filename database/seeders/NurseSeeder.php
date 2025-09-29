<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Nurse;
use App\Models\Department; // If you want to link nurses to departments

class NurseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Find the user created in UserSeeder with the 'nurse' role
        $nurseUser = User::where('email', 'nurse@example.com')->first();
        $department = Department::first(); // Or find a specific department

        if ($nurseUser && !$nurseUser->nurse) { // Check if a nurse profile already exists for this user
            Nurse::create([
                'user_id' => $nurseUser->id,
                'name' => $nurseUser->name, // Use the user's name
                'phone' => $nurseUser->phone, // Use the user's phone
                'specialty' => 'ممرضة طوارئ', // Example specialty
                'bio' => 'ممرضة ذات خبرة في قسم الطوارئ.',
                'image' => 'https://placehold.co/150x150/E0F2F7/000000?text=Nurse',
                'department_id' => $department ? $department->id : null,
            ]);
        }
    }
}

