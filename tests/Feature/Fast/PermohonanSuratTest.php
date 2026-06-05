<?php

use App\Models\Role;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

test('mahasiswa can submit a surat request with an attachment', function () {
    Storage::fake('public');

    $role = Role::create([
        'nama' => 'Mahasiswa',
        'slug' => 'mahasiswa',
    ]);
    $user = User::factory()->create([
        'role_id' => $role->id,
        'email_verified_at' => now(),
    ]);

    $jenisSurat = JenisSurat::create([
        'nama' => 'Surat Keterangan Aktif Kuliah',
        'slug' => 'surat-keterangan-aktif-kuliah',
        'is_active' => true,
        'field_config' => [],
        'alur_pengajuan' => 'submission',
    ]);

    $this->actingAs($user)
        ->post(route('fast.user.submissions.store'), [
            'jenis_surat_id' => $jenisSurat->id,
            'keperluan' => 'Keperluan beasiswa',
            'tanggal_kebutuhan' => now()->addDay()->toDateString(),
            'lampiran' => [UploadedFile::fake()->create('khs.pdf', 256, 'application/pdf')],
        ])
        ->assertRedirect(route('fast.user.dashboard'));

    $this->assertDatabaseHas('jenis_surats', [
        'nama' => 'Surat Keterangan Aktif Kuliah',
    ]);

    $this->assertDatabaseHas('surats', [
        'pemohon_id' => $user->id,
        'keperluan' => 'Keperluan beasiswa',
        'status' => 'pending',
    ]);

    $this->assertDatabaseHas('surat_lampirans', [
        'nama_file' => 'khs.pdf',
        'tipe' => 'application/pdf',
    ]);

    $path = DB::table('surat_lampirans')->value('file_path');

    Storage::disk('public')->assertExists($path);
});
