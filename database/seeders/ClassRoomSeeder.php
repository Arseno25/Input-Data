<?php

namespace Database\Seeders;

use App\Models\ClassRoom;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ClassRoomSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $classNames = ['Teknik Informatika', 'Sistem Informasi'];
        $classCodes = ['A', 'B', 'C', 'D', 'E'];

        foreach ($classNames as $className) {
            foreach ($classCodes as $classCode) {
                ClassRoom::create([
                    'class_name' => $className,
                    'class_code' => $classCode,
                ]);
            }
        }
    }
}
