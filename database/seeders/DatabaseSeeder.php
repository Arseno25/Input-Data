<?php

namespace Database\Seeders;

use App\Models\Room;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\User;
use Database\Seeders\ClassRoomSeeder;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            ClassRoomSeeder::class,
             ShieldSeeder::class
        ]);

        $admin = User::factory()->create([
            'name' => 'Admin',
            'email' => 'admin@gmail.com',
        ]);

        $dosen1 = User::factory()->create([
            'name' => 'dosen 1',
            'email' => 'dosen1@gmail.com',
        ]);
        $dosen2 = User::factory()->create([
            'name' => 'dosen 2',
            'email' => 'dosen2@gmail.com',
        ]);

        $dosen1->assignRole('dosen');
        $dosen2->assignRole('dosen');

        Artisan::call('shield:super-admin', ['--user' => $admin->getKey(), '--tenant' => Room::first()->id]);
        Artisan::call('shield:generate', ['--all' => true, '--panel' => 'admin']);

        Room::get()->each(function (Room $classRoom) use ($admin) {
            $classRoom->users()->attach([$admin->getKey()]);
        });
    }
}
