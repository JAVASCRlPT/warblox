<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create or update admin user
        User::updateOrCreate(
            ['email' => 'admin'],
            [
                'name' => 'Admin Perpustakaan',
                'password' => Hash::make('password'),
                'nim' => null,
                'role' => 'admin',
                'phone' => '081234567890',
            ]
        );

        // Create or update mahasiswa users
        $mahasiswa = [
            [
                'name' => 'Budi Santoso',
                'email' => 'budi@student.test',
                'nim' => '2024001',
                'phone' => '082145678901',
            ],
            [
                'name' => 'Siti Nurhaliza',
                'email' => 'siti@student.test',
                'nim' => '2024002',
                'phone' => '082245678902',
            ],
            [
                'name' => 'Ahmad Wijaya',
                'email' => 'ahmad@student.test',
                'nim' => '2024003',
                'phone' => '082345678903',
            ],
            [
                'name' => 'Eka Putri',
                'email' => 'eka@student.test',
                'nim' => '2024004',
                'phone' => '082445678904',
            ],
            [
                'name' => 'Ricky Pratama',
                'email' => 'ricky@student.test',
                'nim' => '2024005',
                'phone' => '082545678905',
            ],
        ];

        foreach ($mahasiswa as $student) {
            User::updateOrCreate(
                ['email' => $student['email']],
                [
                    'name' => $student['name'],
                    'password' => Hash::make('password'),
                    'nim' => $student['nim'],
                    'role' => 'mahasiswa',
                    'phone' => $student['phone'],
                ]
            );
        }
    }
}
