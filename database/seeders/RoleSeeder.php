<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Role; // تأكد من استيراد موديل Role

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // تحقق مما إذا كانت الأدوار موجودة بالفعل لتجنب التكرار
        if (!Role::where('name', 'admin')->exists()) {
            Role::create(['name' => 'admin', 'description' => 'Administrator with full access']);
        }
        if (!Role::where('name', 'patient')->exists()) {
            Role::create(['name' => 'patient', 'description' => 'Registered patient']);
        }
        if (!Role::where('name', 'doctor')->exists()) {
            Role::create(['name' => 'doctor', 'description' => 'Medical doctor']);
        }
        if (!Role::where('name', 'nurse')->exists()) {
            Role::create(['name' => 'nurse', 'description' => 'Hospital nurse']);
        }
        if (!Role::where('name', 'receptionist')->exists()) {
            Role::create(['name' => 'receptionist', 'description' => 'Front desk receptionist']);
        }
        if (!Role::where('name', 'accountant')->exists()) {
            Role::create(['name' => 'accountant', 'description' => 'Hospital accountant']);
        }
        if (!Role::where('name', 'pharmacist')->exists()) {
            Role::create(['name' => 'pharmacist', 'description' => 'Pharmacist in charge of medicines']);
        }
    }
}