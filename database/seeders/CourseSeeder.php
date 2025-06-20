<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CourseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */    public function run(): void
    {
        $lecturer = \App\Models\User::create([
            'name' => 'Dr. John Doe',
            'email' => 'lecturer@example.com',
            'password' => \Illuminate\Support\Facades\Hash::make('password'),
            'role' => 'lecturer',
        ]);

        \App\Models\Course::create([
            'name' => 'Pemrograman Web',
            'code' => 'WEB101',
            'description' => 'Mata kuliah tentang pengembangan aplikasi web',
            'user_id' => $lecturer->id,
        ]);

        \App\Models\Course::create([
            'name' => 'Basis Data',
            'code' => 'DB101',
            'description' => 'Mata kuliah tentang manajemen basis data',
            'user_id' => $lecturer->id,
        ]);

        \App\Models\Course::create([
            'name' => 'Algoritma dan Pemrograman',
            'code' => 'ALG101',
            'description' => 'Mata kuliah dasar pemrograman',
            'user_id' => $lecturer->id,
        ]);
    }
}
