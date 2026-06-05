<?php

use App\Models\JenisSurat;
use App\Models\Role;
use App\Models\Surat;
use App\Models\SuratTemplate;
use App\Models\User;
use App\Services\FastApprovalWorkflowService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;

uses(RefreshDatabase::class);

function createOutgoingLetterTemplate(JenisSurat $jenisSurat): void
{
    $template = SuratTemplate::create([
        'jenis_surat_id' => $jenisSurat->id,
        'name' => 'Template '.$jenisSurat->nama,
        'slug' => 'template-'.$jenisSurat->id,
        'format' => 'html',
        'subject' => $jenisSurat->nama,
        'template_body' => <<<'HTML'
<section>
    <p>{{yth_list}}</p>
    <p>{{judul_surat}}</p>
    <p>{{isi_ringkas}}</p>
</section>
HTML,
        'version' => 1,
        'is_active' => true,
    ]);

    $template->placeholders()->createMany([
        [
            'placeholder_key' => 'yth_list',
            'label' => 'Daftar YTH',
            'source_type' => 'surat_data',
            'source_key' => 'yth_list',
            'is_required' => true,
        ],
        [
            'placeholder_key' => 'judul_surat',
            'label' => 'Judul Surat',
            'source_type' => 'surat_data',
            'source_key' => 'judul_surat',
            'is_required' => true,
        ],
        [
            'placeholder_key' => 'isi_ringkas',
            'label' => 'Isi Ringkas',
            'source_type' => 'surat_data',
            'source_key' => 'isi_ringkas',
            'is_required' => true,
        ],
    ]);
}

test('admin can open create surat page and preview outgoing letter with dynamic fields', function () {
    $this->withoutVite();

    $adminRole = Role::create([
        'nama' => 'Admin',
        'slug' => 'admin',
    ]);

    $dekanRole = Role::create([
        'nama' => 'Dekan',
        'slug' => 'dekan',
    ]);

    $admin = User::factory()->create([
        'role_id' => $adminRole->id,
        'name' => 'Admin FAST',
    ]);

    $jenisSurat = JenisSurat::create([
        'nama' => 'Surat Keluar Observasi',
        'slug' => 'surat-keluar-observasi',
        'deskripsi' => 'Surat keluar untuk observasi instansi tujuan.',
        'approval_role_id' => $dekanRole->id,
        'perlu_approval' => true,
        'is_active' => true,
        'field_config' => [
            [
                'name' => 'yth_list',
                'label' => 'Daftar YTH',
                'type' => 'repeatable',
                'required' => true,
            ],
            [
                'name' => 'judul_surat',
                'label' => 'Judul Surat',
                'type' => 'text',
                'required' => true,
            ],
            [
                'name' => 'isi_ringkas',
                'label' => 'Isi Ringkas',
                'type' => 'textarea',
                'required' => true,
            ],
        ],
    ]);
    createOutgoingLetterTemplate($jenisSurat);

    $this->actingAs($admin)
        ->get('/admin/surat/create')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('admin/letters/Create')
            ->where('jenisSurats.0.nama', 'Surat Keluar Observasi'));

    $this->actingAs($admin)
        ->post('/admin/surat/preview', [
            'jenis_surat_id' => $jenisSurat->id,
            'keperluan' => 'Pengantar observasi untuk instansi tujuan.',
            'data' => [
                'yth_list' => ['Bapak Dekan', 'Ibu Kaprodi'],
                'judul_surat' => 'Permohonan Observasi',
                'isi_ringkas' => 'Mohon izin untuk melakukan observasi lapangan.',
            ],
        ])
        ->assertRedirect('/admin/surat/preview');

    $this->actingAs($admin)
        ->get('/admin/surat/preview')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('admin/letters/Preview')
            ->where('formData.data.judul_surat', 'Permohonan Observasi')
            ->where('formData.data.yth_list.0', 'Bapak Dekan')
            ->where('formData.data.yth_list.1', 'Ibu Kaprodi'));
});

test('admin can store outgoing surat without approval and pdf is generated immediately', function () {
    Storage::fake('public');
    File::ensureDirectoryExists(storage_path('framework/testing/views'));
    config()->set('view.compiled', storage_path('framework/testing/views'));

    $adminRole = Role::create([
        'nama' => 'Admin',
        'slug' => 'admin',
    ]);

    $admin = User::factory()->create([
        'role_id' => $adminRole->id,
        'name' => 'Admin FAST',
    ]);

    $jenisSurat = JenisSurat::create([
        'nama' => 'Surat Keluar Fakultas',
        'slug' => 'surat-keluar-fakultas',
        'kode_surat' => 'SKF',
        'perlu_approval' => false,
        'is_active' => true,
        'field_config' => [
            [
                'name' => 'yth_list',
                'label' => 'Daftar YTH',
                'type' => 'repeatable',
                'required' => true,
            ],
            [
                'name' => 'judul_surat',
                'label' => 'Judul Surat',
                'type' => 'text',
                'required' => true,
            ],
            [
                'name' => 'isi_ringkas',
                'label' => 'Isi Ringkas',
                'type' => 'textarea',
                'required' => true,
            ],
        ],
    ]);
    createOutgoingLetterTemplate($jenisSurat);

    $this->actingAs($admin)
        ->post('/admin/surat/store', [
            'jenis_surat_id' => $jenisSurat->id,
            'keperluan' => 'Pengantar surat keluar fakultas.',
            'data' => [
                'yth_list' => ['Bapak Dekan', 'Ibu Kaprodi'],
                'judul_surat' => 'Undangan Kegiatan',
                'isi_ringkas' => 'Kami mengundang partisipasi pada kegiatan fakultas.',
            ],
        ])
        ->assertRedirect('/admin/dashboard')
        ->assertSessionHas('success');

    $surat = Surat::query()->latest('id')->firstOrFail();

    expect($surat->type)->toBe('surat_keluar');
    expect($surat->status)->toBe(Surat::STATUS_FINISHED);
    expect($surat->generated_file_type)->toBe('pdf');
    expect($surat->generated_file_path)->not->toBeNull();
    expect($surat->qr_token)->not->toBeNull();

    $this->assertDatabaseHas('surat_data', [
        'surat_id' => $surat->id,
        'field_name' => 'judul_surat',
        'field_value' => 'Undangan Kegiatan',
    ]);

    Storage::disk('public')->assertExists((string) $surat->fresh()->generated_file_path);
});
