<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Invoice;
use App\Models\Patient;
use App\Models\User;
use Carbon\Carbon; // لاستخدام التواريخ والأوقات بسهولة

class InvoiceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // الحصول على بعض المرضى والمستخدمين (كمصدرين للفواتير)
        $patient1 = Patient::whereHas('user', function ($query) {
            $query->where('email', 'patient@example.com');
        })->first();
        $patient2 = Patient::whereHas('user', function ($query) {
            $query->where('email', 'patient.ali@example.com');
        })->first();

        $adminUser = User::where('email', 'admin@example.com')->first();
        $accountantUser = User::where('email', 'accountant@example.com')->first();
        $receptionistUser = User::where('email', 'reception@example.com')->first();


        if (!$patient1 || !$patient2 || !$adminUser || !$accountantUser || !$receptionistUser) {
            $this->command->error('Required users or patients not found. Please run UserSeeder and PatientSeeder first.');
            return;
        }

        $invoicesData = [
            [
                'patient_id' => $patient1->id,
                'issued_by_user_id' => $receptionistUser->id,
                'invoice_number' => 'INV-2025-001',
                'total_amount' => 150.00,
                'paid_amount' => 150.00,
                'status' => 'paid',
                'description' => 'رسوم استشارة طبية',
                'created_at' => Carbon::now()->subDays(10),
                'updated_at' => Carbon::now()->subDays(10),
            ],
            [
                'patient_id' => $patient2->id,
                'issued_by_user_id' => $accountantUser->id,
                'invoice_number' => 'INV-2025-002',
                'total_amount' => 500.00,
                'paid_amount' => 250.00,
                'status' => 'partially_paid',
                'description' => 'رسوم إقامة وعلاج',
                'created_at' => Carbon::now()->subDays(5),
                'updated_at' => Carbon::now()->subDays(3),
            ],
            [
                'patient_id' => $patient1->id,
                'issued_by_user_id' => $adminUser->id,
                'invoice_number' => 'INV-2025-003',
                'total_amount' => 75.50,
                'paid_amount' => 0.00,
                'status' => 'pending',
                'description' => 'تكلفة تحاليل مخبرية',
                'created_at' => Carbon::now()->subDays(2),
                'updated_at' => Carbon::now()->subDays(2),
            ],
        ];

        foreach ($invoicesData as $data) {
            if (!Invoice::where('invoice_number', $data['invoice_number'])->exists()) {
                Invoice::create($data);
            }
        }
    }
}
