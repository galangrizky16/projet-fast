<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Role;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            SuratCategorySeeder::class,
            JenisSuratSeeder::class,
            SuratTemplateSeeder::class,
        ]);

        $mahasiswaRoleId = Role::query()->where('slug', 'mahasiswa')->value('id');

        User::query()->updateOrCreate(
            ['email' => 'test@example.com'],
            [
                'name' => 'Test User',
                'email_verified_at' => now(),
                'password' => Hash::make('password'),
                'remember_token' => Str::random(10),
                'role_id' => $mahasiswaRoleId,
                'nim_nip' => '20210045',
                'nomor_induk' => '20210045',
            ],
        );
    }
}
