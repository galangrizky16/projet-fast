<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\JenisSurat;
use App\Models\Surat;
use App\Models\SuratLampiran;
use App\Services\FastApprovalWorkflowService;
use App\Services\SuratTemplateRendererService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response as HttpResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DashboardController extends Controller
{
    public function __construct(
        protected FastApprovalWorkflowService $workflow,
        protected SuratTemplateRendererService $templateRenderer,
    ) {
    }

    public function index(Request $request): Response
    {
        \Log::info('DashboardController::index called', [
            'user_id' => $request->user()?->id,
        ]);

        $query = Surat::query()
            ->with(['pemohon', 'jenisSurat']);

        $status = $request->string('status')->toString();
        $search = $request->string('search')->trim()->toString();
        $jenisSuratId = $request->integer('jenis_surat_id');

        if ($status !== '') {
            $query->where('status', $status);
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

        // ── TAMBAHAN 1: recent history ─────────────────────────────────
    $recentHistory = [];
    if (\Schema::hasTable('surat_histories')) {
        $recentHistory = \App\Models\SuratHistory::query()
            ->with(['user:id,name', 'surat.jenisSurat:id,nama'])
            ->latest()
            ->limit(10)
            ->get()
            ->map(fn ($h) => [
                'id'           => $h->id,
                'action'       => $h->action,
                'action_label' => $h->action_label,
                'keterangan'   => $h->keterangan,
                'created_at'   => $h->created_at?->toISOString(),
                'user'         => ['name' => $h->user?->name],
                'surat'        => [
                    'id'         => $h->surat?->id,
                    'jenisSurat' => ['nama' => $h->surat?->jenisSurat?->nama],
                ],
            ]);
    }
    // ──────────────────────────────────────────────────────────────

        return Inertia::render('admin/dashboard/Index', [
            'surats' => $surats,
            'summary' => (function (): array {
                $counts = Surat::query()
                    ->selectRaw("
                        COUNT(*) as total,
                        SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as pending,
                        SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as validated,
                        SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as rejected,
                        SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as finished
                    ", [
                        Surat::STATUS_PENDING,
                        Surat::STATUS_VALIDATED_ADMIN,
                        Surat::STATUS_REJECTED,
                        Surat::STATUS_FINISHED,
                    ])
                    ->first();

                return [
                    'total'     => (int) ($counts->total ?? 0),
                    'pending'   => (int) ($counts->pending ?? 0),
                    'validated' => (int) ($counts->validated ?? 0),
                    'rejected'  => (int) ($counts->rejected ?? 0),
                    'finished'  => (int) ($counts->finished ?? 0),
                ];
            })(),
            'filters' => [
                'status' => $status,
                'search' => $search,
                'jenis_surat_id' => $jenisSuratId > 0 ? (string) $jenisSuratId : '',
            ],
            'jenisSurats' => JenisSurat::query()
                ->orderBy('nama')
                ->get(['id', 'nama'])
                ->map(fn (JenisSurat $jenisSurat): array => [
                    'id' => $jenisSurat->id,
                    'nama' => $jenisSurat->nama,
                ])
                ->values(),
            'recentHistory' => $recentHistory, // ← TAMBAHAN 1
        ]);
    }

    public function show(int $id): \Inertia\Response
    {
        $surat = Surat::query()
            ->with(['pemohon', 'jenisSurat', 'lampirans'])
            ->findOrFail($id);

        $isiSurat = json_decode((string) $surat->isi_surat, true);

        return \Inertia\Inertia::render('admin/dashboard/Show', [
            'id' => $surat->id,
            'nomor_surat' => $surat->nomor_surat,
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
                'url' => route('admin.lampiran.preview', $lampiran->id, absolute: false),
                'type' => $lampiran->tipe,
            ])->values(),
            'tanggal_pengajuan' => optional($surat->tanggal_pengajuan ?? $surat->created_at)?->toISOString(),
            'status' => $surat->status,
            'can_approve' => $surat->canBeValidatedByAdmin(),
            'can_edit' => $surat->status === Surat::STATUS_REJECTED && $surat->validated_by_admin_id !== null,
            'previewTemplateUrl' => route('admin.surat.template-preview', $surat->id, absolute: false),
            'generatedDocumentUrl' => filled($surat->generated_file_path)
                ? route('admin.surat.generated-document', $surat->id, absolute: false)
                : null,
        ]);
    }

    public function previewTemplate(int $id): HttpResponse
    {
        $surat = Surat::query()
            ->with(['pemohon', 'jenisSurat.template.placeholders', 'dataEntries'])
            ->findOrFail($id);

        $rendered = $this->templateRenderer->renderForSurat($surat);

        return response(
            $this->templateRenderer->wrapDocumentHtml('Preview '.$surat->jenisSurat?->nama, $rendered['html'], $surat->jenisSurat?->template),
            200,
        )->header('Content-Type', 'text/html; charset=UTF-8');
    }

    // public function previewGeneratedDocument(int $id): StreamedResponse
    // {
    //     $surat = Surat::query()->findOrFail($id);

    //     abort_unless(
    //         filled($surat->generated_file_path) && Storage::disk('public')->exists($surat->generated_file_path),
    //         404,
    //     );

    //     $filename = sprintf(
    //         '%s-%d.pdf',
    //         str_replace(' ', '-', strtolower((string) ($surat->jenisSurat?->nama ?: 'surat'))),
    //         $surat->id,
    //     );

    //     return Storage::disk('public')->response(
    //         $surat->generated_file_path,
    //         $filename,
    //         [
    //             'Content-Type' => 'application/pdf',
    //             'Content-Disposition' => 'inline; filename="'.addslashes($filename).'"',
    //         ],
    //     );
    // }

    public function previewGeneratedDocument(int $id): HttpResponse
    {
        $surat = Surat::query()
            ->with(['pemohon', 'jenisSurat.template.placeholders', 'dataEntries'])
            ->findOrFail($id);

        // Render ulang dari template — selalu fresh, tidak pakai file lama
        $rendered = $this->templateRenderer->renderForSurat($surat);

        return response(
            $this->templateRenderer->wrapDocumentHtml(
                ($surat->jenisSurat?->nama ?? 'Surat') . ' - ' . ($surat->nomor_surat ?? ''),
                $rendered['html'],
                $surat->jenisSurat?->template,
            ),
            200,
        )->header('Content-Type', 'text/html; charset=UTF-8');
    }

    public function downloadPdf(Request $request, int $id): \Illuminate\Http\Response|\Symfony\Component\HttpFoundation\StreamedResponse
    {
        $user  = $request->user();
        $surat = Surat::query()
            ->with(['pemohon', 'jenisSurat.template.placeholders', 'dataEntries'])
            ->findOrFail($id);

        $filename = sprintf(
            '%s-%d.pdf',
            str_replace(' ', '-', strtolower((string) ($surat->jenisSurat?->nama ?: 'surat'))),
            $surat->id,
        );

        // Jika sudah ada file PDF tersimpan → serve langsung
        if (
            filled($surat->generated_file_path) &&
            \Illuminate\Support\Facades\Storage::disk('public')->exists($surat->generated_file_path)
        ) {
            return \Illuminate\Support\Facades\Storage::disk('public')->response(
                $surat->generated_file_path,
                $filename,
                [
                    'Content-Type'        => 'application/pdf',
                    'Content-Disposition' => 'inline; filename="' . addslashes($filename) . '"',
                ],
            );
        }

        // Jika belum ada → generate dulu (UUID + QR + PDF)
        abort_if($user === null, 403);

        $generated = app(\App\Services\SuratDocumentGeneratorService::class)->generate($surat);

        return \Illuminate\Support\Facades\Storage::disk('public')->response(
            $generated->generated_file_path,
            $filename,
            [
                'Content-Type'        => 'application/pdf',
                'Content-Disposition' => 'inline; filename="' . addslashes($filename) . '"',
            ],
        );
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

    public function approve(int $id): RedirectResponse
    {
        $surat = Surat::query()->findOrFail($id);

        $this->workflow->approve($surat, FastApprovalWorkflowService::ROLE_ADMIN, request()->user());

        return back()->with('success', 'Pengajuan berhasil divalidasi admin.');
    }

    public function reject(Request $request, int $id): RedirectResponse
    {
        $request->validate([
            'reason' => ['required', 'string'],
        ]);

        $surat = Surat::query()->findOrFail($id);

        $this->workflow->reject(
            $surat,
            FastApprovalWorkflowService::ROLE_ADMIN,
            $request->user(),
            $request->string('reason')->toString(),
        );

        return back()->with('success', 'Pengajuan berhasil ditolak.');
    }
}
