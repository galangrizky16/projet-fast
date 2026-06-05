<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\JenisSurat;
use App\Models\Surat;
use App\Models\SuratApprovalFlow;
use App\Models\SuratLampiran;
use App\Services\FastApprovalWorkflowService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ApprovalController extends Controller
{
    public function __construct(
        protected FastApprovalWorkflowService $workflow,
    ) {
    }

    public function index(Request $request): Response
    {
        $user = $request->user();
        abort_if($user === null, 403);

        $user->loadMissing('role');

        $roleName = $user->role?->nama ?? 'Approval';
        $roleSlug = $user->role?->slug ?? 'approval';
        $normalizedRole = $this->normalizeRole($roleSlug, $roleName);
        $status = $request->string('status')->toString();
        $search = $request->string('search')->trim()->toString();
        $jenisSuratId = $request->integer('jenis_surat_id');

        $query = $this->baseQueryForRole($normalizedRole);

        if ($status !== '') {
            $query->where('status', $status);
        } else {
            $query->whereIn('status', $this->waitingStatusesForRole($normalizedRole));
        }

        if ($jenisSuratId > 0) {
            $query->where('jenis_surat_id', $jenisSuratId);
        }

        if ($search !== '') {
            $query->whereHas('pemohon', function ($pemohonQuery) use ($search): void {
                $pemohonQuery
                    ->where('name', 'like', "%{$search}%")
                    ->orWhere('nim_nip', 'like', "%{$search}%")
                    ->orWhere('nomor_induk', 'like', "%{$search}%");
            });
        }

        $surats = $query
            ->latest()
            ->paginate(10)
            ->through(fn (Surat $surat): array => [
                'id' => $surat->id,
                'status' => $surat->status,
                'tanggal_pengajuan' => optional($surat->tanggal_pengajuan ?? $surat->created_at)?->toISOString(),
                'created_at' => optional($surat->created_at)?->toISOString(),
                'pemohon' => [
                    'name' => $surat->pemohon?->name,
                    'nim' => $surat->pemohon?->nim_nip ?? $surat->pemohon?->nomor_induk,
                ],
                'jenisSurat' => [
                    'id' => $surat->jenisSurat?->id,
                    'nama' => $surat->jenisSurat?->nama,
                ],
            ])
            ->withQueryString();

        return Inertia::render('admin/approval/Index', [
            'role' => [
                'name' => $roleName,
                'slug' => $roleSlug,
            ],
            'surats' => $surats,
            'summary' => [
                'waiting' => $this->baseSummaryQuery($normalizedRole)->whereIn('status', $this->waitingStatusesForRole($normalizedRole))->count(),
                'approved' => $this->baseSummaryQuery($normalizedRole)->where('status', $this->approvedStatusForRole($normalizedRole))->count(),
                'rejected' => $this->baseSummaryQuery($normalizedRole)->where('status', Surat::STATUS_REJECTED)->count(),
            ],
            'filters' => [
                'status' => $status,
                'search' => $search,
                'jenis_surat_id' => $jenisSuratId > 0 ? (string) $jenisSuratId : '',
            ],
            'jenisSurats' => JenisSurat::query()
                ->where(function ($query) use ($normalizedRole): void {
                    $query->whereHas('approvalRole', function ($roleQuery) use ($normalizedRole): void {
                        $roleQuery
                            ->where('slug', 'like', "%{$normalizedRole}%")
                            ->orWhere('nama', 'like', "%{$normalizedRole}%");
                    });

                    if (in_array($normalizedRole, [FastApprovalWorkflowService::ROLE_KAPRODI, FastApprovalWorkflowService::ROLE_DEKAN], true)) {
                        $query->orWhere('is_active', true);
                    }
                })
                ->whereHas('template')
                ->where(function ($jenisQuery): void {
                    $jenisQuery
                        ->whereNotNull('approval_role_id')
                        ->orWhere('is_active', true);
                })
                ->when($normalizedRole === FastApprovalWorkflowService::ROLE_KAPRODI, function ($jenisQuery): void {
                    $jenisQuery->where(function ($query): void {
                        $query
                            ->whereHas('approvalRole', function ($roleQuery): void {
                                $roleQuery
                                    ->where('slug', 'like', '%kaprodi%')
                                    ->orWhere('nama', 'like', '%kaprodi%');
                            })
                            ->orWhere('is_active', true);
                    });
                })
                ->when($normalizedRole === FastApprovalWorkflowService::ROLE_DEKAN, function ($jenisQuery): void {
                    $jenisQuery->where(function ($query): void {
                        $query
                            ->whereHas('approvalRole', function ($roleQuery): void {
                                $roleQuery
                                    ->where('slug', 'like', '%dekan%')
                                    ->orWhere('nama', 'like', '%dekan%');
                            })
                            ->orWhere('is_active', true);
                    });
                })
                ->orderBy('nama')
                ->get(['id', 'nama'])
                ->map(fn (JenisSurat $jenisSurat): array => [
                    'id' => $jenisSurat->id,
                    'nama' => $jenisSurat->nama,
                ])
                ->values(),
        ]);
    }

    public function show(int $id): JsonResponse
    {
        $surat = Surat::query()
            ->with(['pemohon', 'jenisSurat', 'lampirans'])
            ->findOrFail($id);

        $isiSurat = json_decode((string) $surat->isi_surat, true);

        return response()->json([
            'id' => $surat->id,
            'pemohon' => [
                'name' => $surat->pemohon?->name,
                'nim' => $surat->pemohon?->nim_nip ?? $surat->pemohon?->nomor_induk,
            ],
            'jenis_surat' => $surat->jenisSurat?->nama,
            'keperluan' => $surat->keperluan,
            'isi_surat' => is_array($isiSurat) ? $isiSurat : [],
            'lampiran' => $surat->lampirans->map(fn ($lampiran): array => [
                'id' => $lampiran->id,
                'name' => $lampiran->nama_file,
                'url' => route('approval.lampiran.preview', $lampiran->id, absolute: false),
                'type' => $lampiran->tipe,
            ])->values(),
            'tanggal_pengajuan' => optional($surat->tanggal_pengajuan ?? $surat->created_at)?->toISOString(),
            'status' => $surat->status,
        ]);
    }

    public function previewAttachment(int $id): StreamedResponse
    {
        $lampiran = SuratLampiran::query()->findOrFail($id);

        abort_unless(Storage::disk('public')->exists($lampiran->file_path), 404);

        return Storage::disk('public')->response(
            $lampiran->file_path,
            $lampiran->nama_file,
            [
                'Content-Type' => $lampiran->tipe ?: 'application/octet-stream',
                'Content-Disposition' => 'inline; filename="'.addslashes($lampiran->nama_file).'"',
            ],
        );
    }

    public function approve(Request $request, int $id): RedirectResponse
    {
        $user = $request->user();
        abort_if($user === null, 403);

        $user->loadMissing('role');

        $surat = Surat::query()->findOrFail($id);

        $this->workflow->approve(
            $surat,
            $this->normalizeRole($user->role?->slug, $user->role?->nama),
            $user,
        );

        return back()->with('success', 'Pengajuan berhasil disetujui.');
    }

    public function saveNote(Request $request, int $id): RedirectResponse
    {
        $request->validate(['catatan' => ['required', 'string', 'max:1000']]);

        $user = $request->user();
        abort_if($user === null, 403);
        $user->loadMissing('role');

        $surat = Surat::query()->findOrFail($id);

        $surat->approvalFlows()->create([
            'approver_id'  => $user->id,
            'urutan'       => 0,
            'role'         => $this->normalizeRole($user->role?->slug, $user->role?->nama),
            'status'       => SuratApprovalFlow::STATUS_NOTE,
            'keterangan'   => 'Catatan',
            'catatan'      => $request->string('catatan')->toString(),
            'tanggal_aksi' => now(),
        ]);

        return back()->with('success', 'Catatan berhasil ditambahkan.');
    }

    public function reject(Request $request, int $id): RedirectResponse
    {
        $request->validate([
            'reason' => ['required', 'string'],
        ]);

        $user = $request->user();
        abort_if($user === null, 403);

        $user->loadMissing('role');

        $surat = Surat::query()->findOrFail($id);

        $this->workflow->reject(
            $surat,
            $this->normalizeRole($user->role?->slug, $user->role?->nama),
            $user,
            $request->string('reason')->toString(),
        );

        return back()->with('success', 'Pengajuan berhasil ditolak.');
    }

    private function normalizeRole(?string $slug, ?string $name): string
    {
        $normalizedSlug = Str::slug((string) $slug);
        $normalizedName = Str::slug((string) $name);

        if (Str::contains($normalizedSlug, 'kaprodi') || Str::contains($normalizedName, 'kaprodi')) {
            return FastApprovalWorkflowService::ROLE_KAPRODI;
        }

        return FastApprovalWorkflowService::ROLE_DEKAN;
    }

    private function approvedStatusForRole(string $role): string
    {
        return $role === FastApprovalWorkflowService::ROLE_KAPRODI
            ? Surat::STATUS_APPROVED_KAPRODI
            : Surat::STATUS_APPROVED_DEKAN;
    }

    private function baseSummaryQuery(string $normalizedRole)
    {
        return $this->baseQueryForRole($normalizedRole);
    }

    private function baseQueryForRole(string $normalizedRole)
    {
        return Surat::query()
            ->with(['pemohon', 'jenisSurat.approvalRole'])
            ->whereHas('jenisSurat.approvalRole', function ($roleQuery) use ($normalizedRole): void {
                $roleQuery
                    ->where('slug', 'like', "%{$normalizedRole}%")
                    ->orWhere('nama', 'like', "%{$normalizedRole}%");
            });
    }

    /**
     * @return array<int, string>
     */
    private function waitingStatusesForRole(string $normalizedRole): array
    {
        return [Surat::STATUS_VALIDATED_ADMIN];
    }
}
