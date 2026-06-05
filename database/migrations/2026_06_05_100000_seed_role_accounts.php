<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $roles = DB::table('roles')->pluck('id', 'slug');

        $users = [
            [
                'name'              => 'Admin',
                'email'             => 'tiffanyhellen27@gmail.com',
                'password'          => Hash::make('admin1234'),
                'role_id'           => $roles['admin'] ?? null,
                'status_approval'   => 'approved',
                'email_verified_at' => now(),
                'created_at'        => now(),
                'updated_at'        => now(),
            ],
            [
                'name'              => 'Fanny',
                'email'             => 'hellentiffanyyy@gmail.com',
                'password'          => Hash::make('fanny1234'),
                'role_id'           => $roles['mahasiswa'] ?? null,
                'status_approval'   => 'approved',
                'nim_nip'           => '22eo10001',
                'email_verified_at' => now(),
                'created_at'        => now(),
                'updated_at'        => now(),
            ],
            [
                'name'              => 'Mrs. Laora',
                'email'             => 'hellenfast1@gmail.com',
                'password'          => Hash::make('laora1234'),
                'role_id'           => $roles['dosen'] ?? null,
                'status_approval'   => 'approved',
                'email_verified_at' => now(),
                'created_at'        => now(),
                'updated_at'        => now(),
            ],
            [
                'name'              => 'Mrs. Anna',
                'email'             => 'hellenfast2@gmail.com',
                'password'          => Hash::make('anna1234'),
                'role_id'           => $roles['kaprodi'] ?? null,
                'status_approval'   => 'approved',
                'email_verified_at' => now(),
                'created_at'        => now(),
                'updated_at'        => now(),
            ],
            [
                'name'              => 'Mrs. Moana',
                'email'             => 'hellenfast3@gmail.com',
                'password'          => Hash::make('moana1234'),
                'role_id'           => $roles['dekan'] ?? null,
                'status_approval'   => 'approved',
                'email_verified_at' => now(),
                'created_at'        => now(),
                'updated_at'        => now(),
            ],
        ];

        foreach ($users as $user) {
            if ($user['role_id'] === null) {
                continue;
            }

            DB::table('users')->updateOrInsert(
                ['email' => $user['email']],
                $user
            );
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $emails = [
            'tiffanyhellen27@gmail.com',
            'hellentiffanyyy@gmail.com',
            'hellenfast1@gmail.com',
            'hellenfast2@gmail.com',
            'hellenfast3@gmail.com',
        ];

        DB::table('users')->whereIn('email', $emails)->delete();
    }
};
