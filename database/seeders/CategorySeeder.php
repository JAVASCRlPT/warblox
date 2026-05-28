<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Teknologi & Pemrograman',
                'description' => 'Buku-buku tentang teknologi, pemrograman, dan development',
            ],
            [
                'name' => 'Bisnis & Entrepreneur',
                'description' => 'Buku-buku panduan bisnis dan kewirausahaan',
            ],
            [
                'name' => 'Sains & Pengetahuan Alam',
                'description' => 'Buku-buku tentang sains, fisika, biologi, dan kimia',
            ],
            [
                'name' => 'Sastra & Novel',
                'description' => 'Koleksi novel, puisi, dan karya sastra lainnya',
            ],
            [
                'name' => 'Sejarah & Budaya',
                'description' => 'Buku-buku tentang sejarah, budaya, dan peradaban',
            ],
            [
                'name' => 'Motivasi & Self-Development',
                'description' => 'Buku-buku pengembangan diri dan motivasi pribadi',
            ],
            [
                'name' => 'Pendidikan',
                'description' => 'Buku-buku pendidikan dan pembelajaran',
            ],
            [
                'name' => 'Seni & Desain',
                'description' => 'Buku-buku tentang seni, desain, dan kreativitas',
            ],
        ];

        foreach ($categories as $category) {
            Category::create($category);
        }
    }
}
