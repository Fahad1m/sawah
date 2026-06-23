<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Trip;

class TripSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            [
                'destination' => 'دبي، الإمارات',
                'description' => '5 أيام تشمل برج خليفة، سفاري الصحراء، ومارينا كروز.',
                'price' => 2850, 'duration' => 5, 'rating' => 4.8,
                'image' => 'https://images.unsplash.com/photo-1546412414-8035e1776c9a',
                'features' => ['فندق 5 نجوم','إفطار يومي','سفاري الصحراء','جولة مدينة'],
            ],
            [
                'destination' => 'إسطنبول، تركيا',
                'description' => 'جولة تاريخية والبسفور + جزيرة الأميرات.',
                'price' => 2400, 'duration' => 6, 'rating' => 4.7,
                'image' => 'https://images.unsplash.com/photo-1547996160-81dfa63595aa',
                'features' => ['فندق 4 نجوم','إفطار','رحلة البسفور','مرشد عربي'],
            ],
            [
                'destination' => 'باريس، فرنسا',
                'description' => 'برج إيفل + متحف اللوفر + ديزني لاند (اختياري).',
                'price' => 5200, 'duration' => 7, 'rating' => 4.9,
                'image' => 'https://images.unsplash.com/photo-1502602898657-3e91760cbb34',
                'features' => ['فندق 4 نجوم','تذكرة لوفر','جولة نهارية','نقل من/إلى المطار'],
            ],
            [
                'destination' => 'المالديف',
                'description' => 'منتجع فوق الماء مع وجبات فطور وعشاء.',
                'price' => 9800, 'duration' => 5, 'rating' => 4.9,
                'image' => 'https://images.unsplash.com/photo-1501117716987-c8e5f4d4b1a2',
                'features' => ['منتجع 5 نجوم','نقل بالقارب','عشاء رومانسي'],
            ],
            [
                'destination' => 'تبليسي، جورجيا',
                'description' => 'تبليسي – كازبيجي – جورى – متسخيتا.',
                'price' => 2100, 'duration' => 5, 'rating' => 4.6,
                'image' => 'https://images.unsplash.com/photo-1579519316275-4b8a2e8f4f3b',
                'features' => ['فندق 4 نجوم','رحلات يومية','سائق خاص'],
            ],
            [
                'destination' => 'القاهرة، مصر',
                'description' => 'الأهرامات والمتحف المصري ورحلة نيلية عشاء.',
                'price' => 1850, 'duration' => 4, 'rating' => 4.5,
                'image' => 'https://images.unsplash.com/photo-1544989164-31dc3c645987',
                'features' => ['فندق 4 نجوم','رحلة نيلية','مرشد سياحي'],
            ],
        ];

        foreach ($rows as $row) {
            Trip::updateOrCreate(
                [
                    'destination' => $row['destination'],
                    'price'       => $row['price'],
                    'duration'    => $row['duration'],
                ],
                $row
            );
        }
    }
}
