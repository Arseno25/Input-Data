<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {

        $this->call([
            ShieldSeeder::class,
        ]);

        // Membuat user Admin
        $admin = User::factory()->create([
            'name' => 'Admin',
            'email' => 'admin@gmail.com',
        ]);

        Artisan::call('shield:super-admin', [
            '--user' => $admin->id
        ]);

        $dosen1 = User::factory()->create([
            'name' => 'Dosen1',
            'email' => 'dosen1@gmail.com',
        ]);

        $dosen2 = User::factory()->create([
            'name' => 'Dosen2',
            'email' => 'dosen2@gmail.com',
        ]);

        $dosenRole = 'dosen';
        $dosen1->assignRole($dosenRole);
        $dosen2->assignRole($dosenRole);

        Artisan::call('shield:generate',[
            '--all' => true,
            '--panel' => 1
        ]);
    }
}
