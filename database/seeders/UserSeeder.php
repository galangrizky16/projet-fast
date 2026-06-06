<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Get roles as [slug => id]
        $roles = Role::query()->pluck('id', 'slug')->toArray();

        foreach ($roles as $slug => $roleId) {
            $email = sprintf('%s@example.com', $slug);
            $name = sprintf('%s User', ucfirst($slug));

            $data = [
                'name' => $name,
                'email_verified_at' => now(),
                'password' => Hash::make('password'),
                'remember_token' => Str::random(10),
                'role_id' => $roleId,
            ];

            // Add minimal identifiers for student/lecturer
            if (in_array($slug, ['mahasiswa', 'dosen'], true)) {
                $data['nim_nip'] = '2021' . str_pad((string) $roleId, 4, '0', STR_PAD_LEFT);
                $data['nomor_induk'] = $data['nim_nip'];
            }

            User::query()->updateOrCreate(
                ['email' => $email],
                $data,
            );
        }
    }
}
