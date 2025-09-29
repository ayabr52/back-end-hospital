<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Department; // استيراد موديل Department

class DepartmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // تحقق مما إذا كانت الأقسام موجودة بالفعل لتجنب التكرار
        if (!Department::where('name', 'قسم العناية المركزة')->exists()) {
            Department::create([
                'name' => 'قسم العناية المركزة',
                'description' => 'يقدم رعاية متخصصة للمرضى ذوي الحالات الحرجة.',
                'specialty' => 'العناية المركزة',
            ]);
        }
        if (!Department::where('name', 'قسم النسائية والتوليد')->exists()) {
            Department::create([
                'name' => 'قسم النسائية والتوليد',
                'description' => 'يقدم خدمات رعاية صحة المرأة، الحمل، والولادة.',
                'specialty' => 'نسائية وتوليد',
            ]);
        }
        if (!Department::where('name', 'قسم العمليات الجراحية')->exists()) {
            Department::create([
                'name' => 'قسم العمليات الجراحية',
                'description' => 'يضم غرف العمليات المجهزة لإجراء مختلف أنواع الجراحات.',
                'specialty' => 'جراحة عامة',
            ]);
        }
        if (!Department::where('name', 'قسم القسطرة وجراحة القلب')->exists()) {
            Department::create([
                'name' => 'قسم القسطرة وجراحة القلب',
                'description' => 'متخصص في تشخيص وعلاج أمراض القلب والأوعية الدموية.',
                'specialty' => 'قلبية',
            ]);
        }
        if (!Department::where('name', 'قسم الأطفال')->exists()) {
            Department::create([
                'name' => 'قسم الأطفال',
                'description' => 'يقدم رعاية صحية متكاملة للأطفال من جميع الأعمار.',
                'specialty' => 'أطفال',
            ]);
        }
        if (!Department::where('name', 'قسم الطوارئ')->exists()) {
            Department::create([
                'name' => 'قسم الطوارئ',
                'description' => 'يعمل على مدار الساعة لاستقبال الحالات الطارئة وتقديم الإسعافات الأولية.',
                'specialty' => 'طب طوارئ',
            ]);
        }
    }
}
