<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // \App\Models\User::factory(10)->create();

        // \App\Models\User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);

                $this->call([
            RoleSeeder::class,
            UserSeeder::class,
            DepartmentSeeder::class,
            DoctorSeeder::class,
            PatientSeeder::class,
            AppointmentSeeder::class,
            RoomSeeder::class,
            InvoiceSeeder::class, // إضافة جديدة هنا
            MedicineSeeder::class, // إضافة جديدة هنا
            TaskSeeder::class, // إضافة جديدة هنا
            MedicalRecordSeeder::class, // إضافة جديدة هنا
            LabTestSeeder::class, // إضافة جديدة هنا
            PrescriptionSeeder::class,
            NurseSeeder::class,
        ]);
    }
}
