<?php

namespace Database\Seeders;

use App\Models\Tip;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TipSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tip = Tip::create(['title' => 'التغذية أثناء الرضاعة']);
$tip->items()->createMany([
    ['content' => 'زيادة السعرات الحرارية: تحتاج الأم المرضعة إلى حوالي 300-500 سعرة حرارية إضافية يوميًا.'],
    ['content' => 'التركيز على البروتين: تناول مصادر جيدة للبروتين مثل اللحوم الخالية من الدهون...'],
    // وهكذا
]);

    }
}
