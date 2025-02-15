<?php

namespace Database\Seeders;

use App\Models\Room;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ClassRoomSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $classNames = ['Teknik Informatika'];

        foreach ($classNames as $className) {
                Room::create([
                    'name' => $className,
                ]);
            }
    }
}
