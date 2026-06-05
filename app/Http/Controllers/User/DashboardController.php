<?php
namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\JenisSurat;
use App\Models\Surat;
use App\Models\SuratApprovalFlow;
use App\Models\SuratCategory;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();
        abort_if($user === null, 403);
        $user->loadMissing('role');

        $roleSlug   = str((string) ($user->role?->slug ?? ''))->slug()->toString();
        $isLecturer = str($roleSlug)->contains('dosen');
        $roleId     = $user->role?->id;

        $baseQuery = Surat::query()
            ->with([
                'jenisSurat',
                'approvalFlows' => fn ($q) => $q->with('approver:id,name')->latest('tanggal_aksi')->latest('id'),
            ])
            ->where('pemohon_id', $user->id);

        $jenisSurats = JenisSurat::query()
            ->with('category')
            ->where('is_active', true)
            ->where('alur_pengajuan', 'submission')
            ->when($roleId !== null, fn ($q) => $q->where(fn ($r) => $r
                ->whereNull('allowed_role_id')
                ->orWhere('allowed_role_id', $roleId)
            ))
            ->orderBy('nama')
            ->get(['id', 'category_id', 'nama', 'slug', 'deskripsi', 'field_config']);

        $categoryIds = $jenisSurats->pluck('category_id')->filter()->unique()->values();

        $categories = SuratCategory::query()
            ->where('is_active', true)
            ->when($categoryIds->isNotEmpty(), fn ($q) => $q->whereIn('id', $categoryIds))
            ->orderBy('urutan')
            ->orderBy('nama')
            ->get(['id', 'nama', 'slug', 'deskripsi']);

        // Status "diproses" = semua yang masih berjalan (belum finish/reject)
        $diprosesStatuses = [
            Surat::STATUS_PENDING,
            Surat::STATUS_VALIDATED_ADMIN,
            Surat::STATUS_APPROVED_KAPRODI,
            Surat::STATUS_APPROVED_DEKAN,
        ];

        return Inertia::render('fast/user/Dashboard', [
            'summary' => (function () use ($user, $diprosesStatuses): array {
                $counts = Surat::query()
                    ->where('pemohon_id', $user->id)
                    ->selectRaw("
                        COUNT(*) as total,
                        SUM(CASE WHEN status IN (" . implode(',', array_fill(0, count($diprosesStatuses), '?')) . ") THEN 1 ELSE 0 END) as diproses,
                        SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as selesai,
                        SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as ditolak
                    ", array_merge($diprosesStatuses, [Surat::STATUS_FINISHED, Surat::STATUS_REJECTED]))
                    ->first();

                return [
                    'total'    => (int) ($counts->total ?? 0),
                    'diproses' => (int) ($counts->diproses ?? 0),
                    'selesai'  => (int) ($counts->selesai ?? 0),
                    'ditolak'  => (int) ($counts->ditolak ?? 0),
                ];
            })(),
            'latest' => (clone $baseQuery)
                ->latest('tanggal_pengajuan')
                ->latest('id')
                ->limit(5)
                ->get()
                ->map(fn (Surat $s): array => [
                    'id'              => $s->id,
                    'reference'       => $s->nomor_surat ?: sprintf('REQ-%05d', $s->id),
                    'jenisSurat'      => $s->jenisSurat?->nama ?? 'Surat Akademik',
                    'jenisSuratId'    => $s->jenisSurat?->id,
                    'status'          => $s->status,
                    'keperluan'       => $s->keperluan,
                    'rejectionReason' => $s->approvalFlows
                        ->firstWhere('status', SuratApprovalFlow::STATUS_REJECTED)?->catatan,
                    'notes'           => $s->approvalFlows
                        ->where('status', SuratApprovalFlow::STATUS_NOTE)
                        ->map(fn ($f) => [
                            'catatan'    => $f->catatan,
                            'oleh'       => $f->approver?->name,
                            'created_at' => $f->tanggal_aksi?->toISOString(),
                        ])->values()->all(),
                    'submittedAt'     => optional($s->tanggal_pengajuan ?? $s->created_at)?->toISOString(),
                    'neededAt'        => optional($s->tanggal_kebutuhan)?->toDateString(),
                    'hasPdf'          => filled($s->generated_file_path),
                ])
                ->values(),
            'categories' => $categories->map(fn (SuratCategory $c): array => [
                'id'       => $c->id,
                'nama'     => $c->nama,
                'slug'     => $c->slug,
                'deskripsi'=> $c->deskripsi,
            ])->values(),
            'jenisSurats' => $jenisSurats->map(fn (JenisSurat $j): array => [
                'id'          => $j->id,
                'categoryId'  => $j->category_id,
                'nama'        => $j->nama,
                'slug'        => $j->slug,
                'deskripsi'   => $j->deskripsi,
                'fieldConfig' => collect($j->field_config ?? [])
                    ->filter(fn ($f): bool => is_array($f) && filled($f['name'] ?? null))
                    ->map(fn (array $f): array => [
                        'name'        => (string) $f['name'],
                        'label'       => (string) ($f['label'] ?? $f['name']),
                        'type'        => strtolower((string) ($f['type'] ?? 'text')),
                        'required'    => (bool) ($f['required'] ?? false),
                        'placeholder' => (string) ($f['placeholder'] ?? ''),
                        'options'     => collect($f['options'] ?? [])
                            ->map(fn ($o) => is_array($o)
                                ? ['label' => (string) ($o['label'] ?? $o['value'] ?? ''), 'value' => (string) ($o['value'] ?? '')]
                                : ['label' => (string) $o, 'value' => (string) $o]
                            )->filter(fn ($o) => $o['value'] !== '')->values()->all(),
                    ])->values()->all(),
            ])->values(),
            'userRole' => [
                'id'   => $user->role?->id,
                'name' => $user->role?->nama,
                'slug' => $user->role?->slug,
            ],
            'userProfile' => [
                'name'            => $user->name,
                'identifierLabel' => $isLecturer ? 'NIP' : 'NIM',
                'identifierValue' => $user->nim_nip ?: $user->nomor_induk,
            ],
            'endpoints' => [
                'submission'    => route('fast.user.submissions.store', absolute: false),
                'jenisSuratBase'=> '/jenis-surat',
            ],
        ]);
    }
}