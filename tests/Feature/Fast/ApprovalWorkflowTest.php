<?php

use App\Models\JenisSurat;
use App\Models\Role;
use App\Models\Surat;
use App\Models\SuratTemplate;
use App\Models\User;
use App\Services\FastApprovalWorkflowService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;

uses(RefreshDatabase::class);

function createTestTemplateForJenisSurat(JenisSurat $jenisSurat): void
{
    $template = SuratTemplate::create([
        'jenis_surat_id' => $jenisSurat->id,
        'name' => 'Template Test '.$jenisSurat->nama,
        'slug' => 'template-test-'.$jenisSurat->id,
        'format' => 'html',
        'template_body' => '<p>{{nama_pemohon}} - {{nomor_surat}}</p>',
        'version' => 1,
        'is_active' => true,
    ]);

    $template->placeholders()->createMany([
        [
            'placeholder_key' => 'nama_pemohon',
            'label' => 'Nama Pemohon',
            'source_type' => 'user',
            'source_key' => 'name',
            'is_required' => true,
        ],
        [
            'placeholder_key' => 'nomor_surat',
            'label' => 'Nomor Surat',
            'source_type' => 'surat',
            'source_key' => 'nomor_surat',
            'is_required' => true,
        ],
    ]);
}

test('approval action writes approval flow and updates current surat status', function () {
    $kaprodiRole = Role::create([
        'nama' => 'Kaprodi',
        'slug' => 'kaprodi',
    ]);

    $approverRole = Role::create([
        'nama' => 'Administrasi',
        'slug' => 'administrasi',
    ]);

    $pemohonRole = Role::create([
        'nama' => 'Mahasiswa',
        'slug' => 'mahasiswa',
    ]);

    $approver = User::factory()->create([
        'role_id' => $approverRole->id,
    ]);

    $pemohon = User::factory()->create([
        'role_id' => $pemohonRole->id,
    ]);

    $jenisSurat = JenisSurat::create([
        'nama' => 'Surat Keterangan Mahasiswa Aktif',
        'approval_role_id' => $kaprodiRole->id,
        'perlu_approval' => true,
    ]);

    $surat = Surat::create([
        'jenis_surat_id' => $jenisSurat->id,
        'pemohon_id' => $pemohon->id,
        'keperluan' => 'Keperluan pengajuan beasiswa akademik semester berjalan.',
        'status' => Surat::STATUS_PENDING,
        'tanggal_pengajuan' => now(),
        'tanggal_kebutuhan' => now()->addDays(2)->toDateString(),
    ]);

    app(FastApprovalWorkflowService::class)->approve(
        $surat->fresh(),
        FastApprovalWorkflowService::ROLE_ADMIN,
        $approver,
    );

    $this->assertDatabaseHas('surats', [
        'id' => $surat->id,
        'status' => Surat::STATUS_VALIDATED_ADMIN,
    ]);

    $this->assertDatabaseHas('surat_approval_flows', [
        'surat_id' => $surat->id,
        'approver_id' => $approver->id,
        'urutan' => 1,
        'role' => FastApprovalWorkflowService::ROLE_ADMIN,
        'status' => 'approved',
    ]);
});

test('admin approval can finish surat immediately when no final approval role is required', function () {
    Storage::fake('public');

    $adminRole = Role::create([
        'nama' => 'Administrasi',
        'slug' => 'administrasi',
    ]);

    $pemohonRole = Role::create([
        'nama' => 'Mahasiswa',
        'slug' => 'mahasiswa',
    ]);

    $admin = User::factory()->create([
        'role_id' => $adminRole->id,
    ]);

    $pemohon = User::factory()->create([
        'role_id' => $pemohonRole->id,
    ]);

    $jenisSurat = JenisSurat::create([
        'nama' => 'Surat Keterangan Internal',
        'perlu_approval' => false,
        'kode_surat' => 'INTERNAL',
    ]);
    createTestTemplateForJenisSurat($jenisSurat);

    $surat = Surat::create([
        'jenis_surat_id' => $jenisSurat->id,
        'pemohon_id' => $pemohon->id,
        'keperluan' => 'Keperluan administrasi internal yang cukup divalidasi admin.',
        'status' => Surat::STATUS_PENDING,
        'tanggal_pengajuan' => now(),
        'tanggal_kebutuhan' => now()->addDays(2)->toDateString(),
    ]);

    app(FastApprovalWorkflowService::class)->approve(
        $surat->fresh(),
        FastApprovalWorkflowService::ROLE_ADMIN,
        $admin,
    );

    $this->assertDatabaseHas('surats', [
        'id' => $surat->id,
        'status' => Surat::STATUS_FINISHED,
        'generated_file_type' => 'pdf',
    ]);
    expect($surat->fresh()->rendered_snapshot)->toContain('B/');
    expect($surat->fresh()->generated_file_path)->not->toBeNull();
    Storage::disk('public')->assertExists((string) $surat->fresh()->generated_file_path);
});

test('only configured final approval role can approve validated surat', function () {
    $adminRole = Role::create([
        'nama' => 'Administrasi',
        'slug' => 'administrasi',
    ]);

    $kaprodiRole = Role::create([
        'nama' => 'Kaprodi',
        'slug' => 'kaprodi',
    ]);

    $dekanRole = Role::create([
        'nama' => 'Dekan',
        'slug' => 'dekan',
    ]);

    $pemohonRole = Role::create([
        'nama' => 'Mahasiswa',
        'slug' => 'mahasiswa',
    ]);

    $pemohon = User::factory()->create([
        'role_id' => $pemohonRole->id,
    ]);

    $jenisSurat = JenisSurat::create([
        'nama' => 'Surat Fakultas',
        'approval_role_id' => $dekanRole->id,
        'perlu_approval' => true,
    ]);

    $surat = Surat::create([
        'jenis_surat_id' => $jenisSurat->id,
        'pemohon_id' => $pemohon->id,
        'keperluan' => 'Keperluan surat resmi tingkat fakultas.',
        'status' => Surat::STATUS_VALIDATED_ADMIN,
        'tanggal_pengajuan' => now(),
        'tanggal_kebutuhan' => now()->addDays(2)->toDateString(),
    ]);

    expect($surat->fresh()->canBeApprovedByRole(FastApprovalWorkflowService::ROLE_KAPRODI))->toBeFalse();
    expect($surat->fresh()->canBeApprovedByRole(FastApprovalWorkflowService::ROLE_DEKAN))->toBeTrue();
});

test('user dashboard returns rejection reason from latest rejected approval flow', function () {
    $this->withoutVite();

    $pemohonRole = Role::create([
        'nama' => 'Mahasiswa',
        'slug' => 'mahasiswa',
    ]);

    $adminRole = Role::create([
        'nama' => 'Administrasi',
        'slug' => 'administrasi',
    ]);

    $pemohon = User::factory()->create([
        'role_id' => $pemohonRole->id,
        'name' => 'Fanny',
    ]);

    $admin = User::factory()->create([
        'role_id' => $adminRole->id,
    ]);

    $jenisSurat = JenisSurat::create([
        'nama' => 'Surat Rekomendasi Akademik',
        'field_config' => [],
    ]);

    $surat = Surat::create([
        'jenis_surat_id' => $jenisSurat->id,
        'pemohon_id' => $pemohon->id,
        'keperluan' => 'Pengajuan surat untuk kebutuhan seleksi program pertukaran pelajar.',
        'status' => Surat::STATUS_REJECTED,
        'tanggal_pengajuan' => now(),
        'tanggal_kebutuhan' => now()->addDays(5)->toDateString(),
    ]);

    $surat->approvalFlows()->create([
        'approver_id' => $admin->id,
        'urutan' => 1,
        'role' => FastApprovalWorkflowService::ROLE_ADMIN,
        'status' => 'rejected',
        'keterangan' => 'Ditolak ADMIN',
        'catatan' => 'Dokumen pendukung belum lengkap.',
        'approved_at' => now(),
        'tanggal_aksi' => now(),
    ]);

    $this->actingAs($pemohon)
        ->get(route('fast.user.dashboard'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('fast/user/Dashboard')
            ->where('userRole.name', 'Mahasiswa')
            ->where('userProfile.identifierLabel', 'NIM')
            ->where('latest.0.status', Surat::STATUS_REJECTED)
            ->where('latest.0.rejectionReason', 'Dokumen pendukung belum lengkap.'));
});

test('dosen dashboard uses the same page with lecturer specific profile metadata', function () {
    $this->withoutVite();

    $dosenRole = Role::create([
        'nama' => 'Dosen',
        'slug' => 'dosen',
    ]);

    $dosen = User::factory()->create([
        'role_id' => $dosenRole->id,
        'name' => 'Rina Pratama',
        'nim_nip' => '1987001122',
    ]);

    $this->actingAs($dosen)
        ->get(route('fast.user.dashboard'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('fast/user/Dashboard')
            ->where('userRole.name', 'Dosen')
            ->where('userProfile.identifierLabel', 'NIP')
            ->where('userProfile.identifierValue', '1987001122'));
});

test('admin dashboard shows pending and validated surat when no status filter is selected', function () {
    $this->withoutVite();

    $adminRole = Role::create([
        'nama' => 'Admin',
        'slug' => 'admin',
    ]);

    $mahasiswaRole = Role::create([
        'nama' => 'Mahasiswa',
        'slug' => 'mahasiswa',
    ]);

    $admin = User::factory()->create([
        'role_id' => $adminRole->id,
    ]);

    $pemohon = User::factory()->create([
        'role_id' => $mahasiswaRole->id,
        'name' => 'Fanny',
    ]);

    $jenisSurat = JenisSurat::create([
        'nama' => 'Surat Akademik',
    ]);

    Surat::create([
        'jenis_surat_id' => $jenisSurat->id,
        'pemohon_id' => $pemohon->id,
        'keperluan' => 'Pengajuan surat pertama.',
        'status' => Surat::STATUS_PENDING,
        'tanggal_pengajuan' => now(),
        'tanggal_kebutuhan' => now()->addDay()->toDateString(),
    ]);

    Surat::create([
        'jenis_surat_id' => $jenisSurat->id,
        'pemohon_id' => $pemohon->id,
        'keperluan' => 'Pengajuan surat kedua.',
        'status' => Surat::STATUS_VALIDATED_ADMIN,
        'tanggal_pengajuan' => now()->subMinute(),
        'tanggal_kebutuhan' => now()->addDays(2)->toDateString(),
    ]);

    $this->actingAs($admin)
        ->get(route('admin.dashboard'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('admin/dashboard/Index')
            ->where('filters.status', '')
            ->has('surats.data', 2)
            ->where('surats.data.0.status', Surat::STATUS_PENDING)
            ->where('surats.data.1.status', Surat::STATUS_VALIDATED_ADMIN));
});

test('approval dashboard shows only validated surat for the signed in approval role', function () {
    $this->withoutVite();

    $kaprodiRole = Role::create([
        'nama' => 'Kaprodi',
        'slug' => 'kaprodi',
    ]);

    $dekanRole = Role::create([
        'nama' => 'Dekan',
        'slug' => 'dekan',
    ]);

    $mahasiswaRole = Role::create([
        'nama' => 'Mahasiswa',
        'slug' => 'mahasiswa',
    ]);

    $kaprodi = User::factory()->create([
        'role_id' => $kaprodiRole->id,
    ]);

    $pemohon = User::factory()->create([
        'role_id' => $mahasiswaRole->id,
        'name' => 'Fanny',
    ]);

    $suratUntukKaprodi = Surat::create([
        'jenis_surat_id' => JenisSurat::create([
            'nama' => 'Surat Kaprodi',
            'approval_role_id' => $kaprodiRole->id,
            'perlu_approval' => true,
        ])->id,
        'pemohon_id' => $pemohon->id,
        'keperluan' => 'Butuh approval kaprodi.',
        'status' => Surat::STATUS_VALIDATED_ADMIN,
        'tanggal_pengajuan' => now(),
        'tanggal_kebutuhan' => now()->addDay()->toDateString(),
    ]);

    Surat::create([
        'jenis_surat_id' => JenisSurat::create([
            'nama' => 'Surat Dekan',
            'approval_role_id' => $dekanRole->id,
            'perlu_approval' => true,
        ])->id,
        'pemohon_id' => $pemohon->id,
        'keperluan' => 'Butuh approval dekan.',
        'status' => Surat::STATUS_VALIDATED_ADMIN,
        'tanggal_pengajuan' => now(),
        'tanggal_kebutuhan' => now()->addDays(2)->toDateString(),
    ]);

    Surat::create([
        'jenis_surat_id' => $suratUntukKaprodi->jenis_surat_id,
        'pemohon_id' => $pemohon->id,
        'keperluan' => 'Masih pending admin.',
        'status' => Surat::STATUS_PENDING,
        'tanggal_pengajuan' => now()->subDay(),
        'tanggal_kebutuhan' => now()->addDays(3)->toDateString(),
    ]);

    $this->actingAs($kaprodi)
        ->get(route('approval.dashboard'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('admin/approval/Index')
            ->where('role.name', 'Kaprodi')
            ->where('filters.status', Surat::STATUS_VALIDATED_ADMIN)
            ->where('summary.waiting', 1)
            ->has('surats.data', 1)
            ->where('surats.data.0.id', $suratUntukKaprodi->id)
            ->where('surats.data.0.status', Surat::STATUS_VALIDATED_ADMIN));
});

test('kaprodi can approve validated surat from approval dashboard', function () {
    Storage::fake('public');

    $kaprodiRole = Role::create([
        'nama' => 'Kaprodi',
        'slug' => 'kaprodi',
    ]);

    $mahasiswaRole = Role::create([
        'nama' => 'Mahasiswa',
        'slug' => 'mahasiswa',
    ]);

    $kaprodi = User::factory()->create([
        'role_id' => $kaprodiRole->id,
    ]);

    $pemohon = User::factory()->create([
        'role_id' => $mahasiswaRole->id,
    ]);

    $jenisSurat = JenisSurat::create([
        'nama' => 'Surat Pengajuan Kaprodi',
        'approval_role_id' => $kaprodiRole->id,
        'perlu_approval' => true,
        'kode_surat' => 'KAPRODI',
    ]);
    createTestTemplateForJenisSurat($jenisSurat);

    $surat = Surat::create([
        'jenis_surat_id' => $jenisSurat->id,
        'pemohon_id' => $pemohon->id,
        'keperluan' => 'Perlu persetujuan kaprodi.',
        'status' => Surat::STATUS_VALIDATED_ADMIN,
        'tanggal_pengajuan' => now(),
        'tanggal_kebutuhan' => now()->addDay()->toDateString(),
    ]);

    $this->actingAs($kaprodi)
        ->post(route('approval.surat.approve', $surat->id))
        ->assertRedirect();

    $this->assertDatabaseHas('surats', [
        'id' => $surat->id,
        'status' => Surat::STATUS_FINISHED,
        'generated_file_type' => 'pdf',
    ]);

    expect($surat->fresh()->rendered_snapshot)->toContain('B/');
    Storage::disk('public')->assertExists((string) $surat->fresh()->generated_file_path);
});

test('admin can preview rendered template for a surat', function () {
    $this->withoutVite();

    $adminRole = Role::create([
        'nama' => 'Admin',
        'slug' => 'admin',
    ]);

    $mahasiswaRole = Role::create([
        'nama' => 'Mahasiswa',
        'slug' => 'mahasiswa',
    ]);

    $admin = User::factory()->create([
        'role_id' => $adminRole->id,
    ]);

    $pemohon = User::factory()->create([
        'role_id' => $mahasiswaRole->id,
        'name' => 'Rina Pratama',
    ]);

    $jenisSurat = JenisSurat::create([
        'nama' => 'Permohonan Akademik',
        'kode_surat' => 'AKD',
    ]);
    createTestTemplateForJenisSurat($jenisSurat);

    $surat = Surat::create([
        'jenis_surat_id' => $jenisSurat->id,
        'pemohon_id' => $pemohon->id,
        'nomor_surat' => 'B/0001/AKD/FMIKOM/2026',
        'keperluan' => 'Preview template surat.',
        'status' => Surat::STATUS_PENDING,
        'tanggal_pengajuan' => now(),
        'tanggal_kebutuhan' => now()->addDay()->toDateString(),
    ]);

    $this->actingAs($admin)
        ->get(route('admin.surat.template-preview', $surat->id))
        ->assertOk()
        ->assertSee('Rina Pratama')
        ->assertSee('B/0001/AKD/FMIKOM/2026');
});

test('admin can manage active template per jenis surat', function () {
    $this->withoutVite();

    $adminRole = Role::create([
        'nama' => 'Admin',
        'slug' => 'admin',
    ]);

    $admin = User::factory()->create([
        'role_id' => $adminRole->id,
    ]);

    $jenisSurat = JenisSurat::create([
        'nama' => 'Permohonan Akademik',
        'slug' => 'permohonan-akademik',
    ]);
    createTestTemplateForJenisSurat($jenisSurat);

    $this->actingAs($admin)
        ->get(route('admin.templates.index', ['jenis_surat_id' => $jenisSurat->id]))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('admin/templates/Index')
            ->where('selectedJenisSurat.id', $jenisSurat->id)
            ->where('selectedJenisSurat.template.name', 'Template Test '.$jenisSurat->nama)
            ->has('selectedJenisSurat.template.placeholders', 2));
});

test('admin can update active template and preview generated pdf document', function () {
    $this->withoutVite();
    Storage::fake('public');

    $adminRole = Role::create([
        'nama' => 'Admin',
        'slug' => 'admin',
    ]);

    $mahasiswaRole = Role::create([
        'nama' => 'Mahasiswa',
        'slug' => 'mahasiswa',
    ]);

    $admin = User::factory()->create([
        'role_id' => $adminRole->id,
    ]);

    $pemohon = User::factory()->create([
        'role_id' => $mahasiswaRole->id,
        'name' => 'Dian Larasati',
    ]);

    $jenisSurat = JenisSurat::create([
        'nama' => 'Permohonan Akademik',
        'slug' => 'permohonan-akademik',
        'kode_surat' => 'AKD',
        'perlu_approval' => false,
    ]);
    createTestTemplateForJenisSurat($jenisSurat);

    $this->actingAs($admin)
        ->put(route('admin.templates.update', $jenisSurat->id), [
            'name' => 'Template Akademik Revisi',
            'source_reference' => 'ref.docx',
            'subject' => 'Surat Akademik',
            'template_header' => '<p>Header</p>',
            'template_body' => '<p>{{nama_pemohon}}</p><p>{{nomor_surat}}</p>',
            'template_footer' => '<p>Footer</p>',
        ])
        ->assertRedirect(route('admin.templates.index', ['jenis_surat_id' => $jenisSurat->id]));

    $template = $jenisSurat->template()->firstOrFail();

    expect($template->name)->toBe('Template Akademik Revisi');
    expect($template->version)->toBe(2);

    $surat = Surat::create([
        'jenis_surat_id' => $jenisSurat->id,
        'pemohon_id' => $pemohon->id,
        'keperluan' => 'Perlu surat akademik.',
        'status' => Surat::STATUS_PENDING,
        'tanggal_pengajuan' => now(),
        'tanggal_kebutuhan' => now()->addDay()->toDateString(),
    ]);

    $this->actingAs($admin)
        ->post(route('admin.surat.approve', $surat->id))
        ->assertRedirect();

    $surat = $surat->fresh();

    Storage::disk('public')->assertExists((string) $surat->generated_file_path);

    $this->actingAs($admin)
        ->get(route('admin.surat.generated-document', $surat->id))
        ->assertOk()
        ->assertHeader('content-type', 'text/html; charset=UTF-8');
});
