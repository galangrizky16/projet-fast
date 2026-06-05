<?php

use App\Models\JenisSurat;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;

test('mahasiswa and dosen can view ajukan surat page', function (string $roleName, string $roleSlug, string $identifierLabel) {
    $this->withoutVite();

    $role = Role::create([
        'nama' => $roleName,
        'slug' => $roleSlug,
    ]);
    $user = User::factory()->create([
        'role_id' => $role->id,
        'nim_nip' => '2109012344',
        'name' => 'Adrian Muhammad',
    ]);

    $this->actingAs($user)
        ->get(route('fast.user.dashboard'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('fast/user/Dashboard')
            ->where('userProfile.name', 'Adrian Muhammad')
            ->where('userRole.name', $roleName)
            ->where('userProfile.identifierLabel', $identifierLabel)
            ->where('summary.total', 0)
        );
})->with([
    'mahasiswa' => ['Mahasiswa', 'mahasiswa', 'NIM'],
    'dosen' => ['Dosen', 'dosen', 'NIP'],
]);

test('role outside mahasiswa and dosen cannot access ajukan surat page', function () {
    $role = Role::create([
        'nama' => 'Administrasi Akademik',
        'slug' => 'administrasi',
    ]);
    $user = User::factory()->create([
        'role_id' => $role->id,
    ]);

    $this->actingAs($user)
        ->get(route('fast.user.dashboard'), ['Accept' => 'application/json'])
        ->assertForbidden();
});

test('academic user can submit surat with png attachment', function () {
    Storage::fake('public');

    $userRole = Role::create([
        'nama' => 'Dosen',
        'slug' => 'dosen',
    ]);
    $user = User::factory()->create([
        'role_id' => $userRole->id,
        'name' => 'Rina Pratama',
        'nim_nip' => '1987001122',
    ]);

    $jenisSurat = JenisSurat::create([
        'nama' => 'Surat Izin Penelitian',
        'slug' => 'surat-izin-penelitian',
        'is_active' => true,
        'field_config' => [],
        'alur_pengajuan' => 'submission',
    ]);

    $this->actingAs($user)
        ->post(route('fast.user.submissions.store'), [
            'jenis_surat_id' => $jenisSurat->id,
            'keperluan' => 'Pengajuan surat untuk kebutuhan penelitian dosen pembimbing lapangan.',
            'tanggal_kebutuhan' => now()->addDays(3)->toDateString(),
            'lampiran' => [UploadedFile::fake()->image('surat.png')],
        ])
        ->assertRedirect(route('fast.user.dashboard'))
        ->assertSessionHas('success');

    $this->assertDatabaseHas('jenis_surats', [
        'nama' => 'Surat Izin Penelitian',
    ]);

    $this->assertDatabaseHas('surats', [
        'pemohon_id' => $user->id,
        'keperluan' => 'Pengajuan surat untuk kebutuhan penelitian dosen pembimbing lapangan.',
        'status' => 'pending',
    ]);

    $this->assertDatabaseHas('surat_lampirans', [
        'nama_file' => 'surat.png',
        'tipe' => 'image/png',
    ]);

    $path = DB::table('surat_lampirans')->value('file_path');
    Storage::disk('public')->assertExists($path);
});

test('ajukan surat validates invalid attachment format', function () {
    Storage::fake('public');

    $role = Role::create([
        'nama' => 'Mahasiswa',
        'slug' => 'mahasiswa',
    ]);
    $user = User::factory()->create([
        'role_id' => $role->id,
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
            'keperluan' => 'Pengajuan surat untuk melengkapi administrasi kampus semester berjalan.',
            'tanggal_kebutuhan' => now()->addDay()->toDateString(),
            'lampiran' => [UploadedFile::fake()->create('lampiran.txt', 16, 'text/plain')],
        ])
        ->assertSessionHasErrors('lampiran.0');
});

test('approved surat can be downloaded by owner', function () {
    Storage::fake('public');
    Storage::disk('public')->put('test.pdf', 'PDF contents');

    $role = Role::create([
        'nama' => 'Mahasiswa',
        'slug' => 'mahasiswa',
    ]);
    $user = User::factory()->create([
        'role_id' => $role->id,
    ]);

    $suratId = DB::table('surats')->insertGetId([
        'jenis_surat_id' => DB::table('jenis_surats')->insertGetId([
            'nama' => 'Surat Keterangan Aktif Kuliah',
            'created_at' => now(),
            'updated_at' => now(),
        ]),
        'pemohon_id' => $user->id,
        'nomor_surat' => 'ASRT-20260001',
        'keperluan' => 'Keperluan administrasi akademik semester ini.',
        'status' => 'approved',
        'generated_file_path' => 'test.pdf',
        'tanggal_pengajuan' => now(),
        'tanggal_kebutuhan' => now()->addDay()->toDateString(),
        'qr_token' => (string) \Illuminate\Support\Str::uuid(),
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $this->actingAs($user)
        ->get(route('admin.surat.pdf', $suratId))
        ->assertOk();
});
