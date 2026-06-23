<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\TripRequest;
use Illuminate\Support\Carbon;

class TripRequestSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            [
                'user_name' => 'وليد', 'user_email' => 'waleed@example.com',
                'destination' => 'إسطنبول، تركيا',
                'date' => Carbon::parse('2025-10-15'),
                'people' => 2, 'budget' => 5000, 'status' => 'pending',
                'details' => 'شهر عسل، نفضل غرفة بإطلالة.',
            ],
            [
                'user_name' => 'نورة', 'user_email' => 'noura@example.com',
                'destination' => 'دبي، الإمارات',
                'date' => Carbon::parse('2025-11-01'),
                'people' => 4, 'budget' => 7000, 'status' => 'approved',
                'details' => 'عائلة مع طفلين، نحتاج جولة مائية.',
            ],
            [
                'user_name' => 'سلمان', 'user_email' => 'salman@example.com',
                'destination' => 'القاهرة، مصر',
                'date' => Carbon::parse('2025-12-20'),
                'people' => 3, 'budget' => 4500, 'status' => 'rejected',
                'details' => 'برنامج اقتصادي قدر الإمكان.',
            ],
        ];

        foreach ($rows as $row) {
            TripRequest::updateOrCreate(
                [
                    'user_name'  => $row['user_name'],
                    'destination'=> $row['destination'],
                    'date'       => $row['date'],
                ],
                $row
            );
        }
    }
}
