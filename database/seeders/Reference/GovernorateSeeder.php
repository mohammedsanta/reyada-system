<?php

namespace Database\Seeders\Reference;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class GovernorateSeeder extends Seeder
{
    public function run(): void
    {
        $governorates = [
            ['القاهرة', 'Cairo'],
            ['الجيزة', 'Giza'],
            ['الإسكندرية', 'Alexandria'],
            ['الدقهلية', 'Dakahlia'],
            ['البحر الأحمر', 'Red Sea'],
            ['البحيرة', 'Beheira'],
            ['الفيوم', 'Faiyum'],
            ['الغربية', 'Gharbia'],
            ['الإسماعيلية', 'Ismailia'],
            ['المنوفية', 'Monufia'],
            ['المنيا', 'Minya'],
            ['القليوبية', 'Qalyubia'],
            ['الوادي الجديد', 'New Valley'],
            ['السويس', 'Suez'],
            ['أسوان', 'Aswan'],
            ['أسيوط', 'Assiut'],
            ['بني سويف', 'Beni Suef'],
            ['بورسعيد', 'Port Said'],
            ['دمياط', 'Damietta'],
            ['الشرقية', 'Sharqia'],
            ['جنوب سيناء', 'South Sinai'],
            ['كفر الشيخ', 'Kafr El Sheikh'],
            ['مطروح', 'Matrouh'],
            ['الأقصر', 'Luxor'],
            ['قنا', 'Qena'],
            ['شمال سيناء', 'North Sinai'],
            ['سوهاج', 'Sohag'],
        ];

        foreach ($governorates as [$name, $nameEn]) {
            DB::table('governorates')->insert([
                'name' => $name,
                'name_en' => $nameEn,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}