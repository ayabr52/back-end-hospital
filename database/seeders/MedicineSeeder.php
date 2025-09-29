<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Medicine;
use Carbon\Carbon; // لاستخدام التواريخ

class MedicineSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $medicinesData = [
            [
                'name' => 'Paracetamol 500mg',
                'generic_name' => 'Paracetamol',
                'manufacturer' => 'PharmaCo',
                'dosage_form' => 'Tablet',
                'strength' => '500mg',
                'stock_quantity' => 1000,
                'price' => 5.50,
                'expiry_date' => Carbon::now()->addYears(2),
                'description' => 'مسكن للألم وخافض للحرارة.',
            ],
            [
                'name' => 'Amoxicillin 250mg/5ml',
                'generic_name' => 'Amoxicillin',
                'manufacturer' => 'Global Pharma',
                'dosage_form' => 'Syrup',
                'strength' => '250mg/5ml',
                'stock_quantity' => 200,
                'price' => 12.75,
                'expiry_date' => Carbon::now()->addMonths(18),
                'description' => 'مضاد حيوي واسع الطيف.',
            ],
            [
                'name' => 'Ibuprofen 400mg',
                'generic_name' => 'Ibuprofen',
                'manufacturer' => 'HealthMed',
                'dosage_form' => 'Tablet',
                'strength' => '400mg',
                'stock_quantity' => 750,
                'price' => 7.25,
                'expiry_date' => Carbon::now()->addYears(1),
                'description' => 'مضاد للالتهابات غير ستيرويدي.',
            ],
            [
                'name' => 'Insulin Glargine',
                'generic_name' => 'Insulin Glargine',
                'manufacturer' => 'BioGen',
                'dosage_form' => 'Injection',
                'strength' => '100 units/ml',
                'stock_quantity' => 50,
                'price' => 150.00,
                'expiry_date' => Carbon::now()->addMonths(6),
                'description' => 'أنسولين طويل المفعول لمرضى السكري.',
            ],
        ];

        foreach ($medicinesData as $data) {
            if (!Medicine::where('name', $data['name'])->exists()) {
                Medicine::create($data);
            }
        }
    }
}
