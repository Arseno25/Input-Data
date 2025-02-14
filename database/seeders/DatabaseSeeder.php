<?php

namespace Database\Seeders;

use App\Models\ClassRoom;
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
            ShieldSeeder::class,
        ]);

        // Membuat user Admin
        $user = User::factory()->create([
            'name' => 'Admin',
            'email' => 'admin@gmail.com',
        ]);

        Artisan::call('shield:super-admin', [
            '--user' => $user->id,
            '--tenant' => '1',
        ]);

        Artisan::call('shield:generate',[
            '--all' => true,
            '--panel' => 'admin'
        ]);

        ClassRoom::get()->each(function (ClassRoom $class) use ($user) {
            $class->users()->attach($user);
        });
    }
}
