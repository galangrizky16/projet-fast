<?php
// File: app/Http/Controllers/Admin/DashboardController.php
// Menggantikan: app/Http/Controllers/Fast/Administrasi/AdminDashboardController.php
// PERUBAHAN: namespace, class name, Inertia render path

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
    ) {}

    public function index(Request $request): Response
    {
        $query  = Surat::query()->with(['pemohon', 'jenisSurat']);
        $status       = $request->string('status')->toString();
        $search       = $request->string('search')->trim()->toString();
        $jenisSuratId = $request->integer('jenis_surat_id');

        if ($status !== '') $query->where('status', $status);
        if ($jenisSuratId > 0) $query->where('jenis_surat_id', $jenisSuratId);
        if ($search !== '') {
            $query->whereHas('pemohon', fn ($q) => $q
                ->where('name', 'like', "%{$search}%")
                ->orWhere('nim_nip', 'like', "%{$search}%")
                ->orWhere('nomor_induk', 'like', "%{$search}%")
            );
        }

        $surats = $query->latest()->paginate(10)
            ->through(fn (Surat $surat): array => [
                'id'                => $surat->id,
                'status'            => $surat->status,
                'tanggal_pengajuan' => optional($surat->tanggal_pengajuan ?? $surat->created_at)?->toISOString(),
                'created_at'        => optional($surat->created_at)?->toISOString(),
                'pemohon'           => [
                    'name' => $surat->pemohon?->name,
                    'nim'  => $surat->pemohon?->nim_nip ?? $surat->pemohon?->nomor_induk,
                ],
                'jenisSurat'        => [
                    'id'   => $surat->jenisSurat?->id,
                    'nama' => $surat->jenisSurat?->nama,
                ],
            ])
            ->withQueryString();

        // ── Render path diupdate ──
        return Inertia::render('admin/dashboard/Index', [
            'surats'      => $surats,
            'summary'     => [
                'total'     => Surat::query()->count(),
                'pending'   => Surat::query()->where('status', Surat::STATUS_PENDING)->count(),
                'validated' => Surat::query()->where('status', Surat::STATUS_VALIDATED_ADMIN)->count(),
                'rejected'  => Surat::query()->where('status', Surat::STATUS_REJECTED)->count(),
                'finished'  => Surat::query()->where('status', Surat::STATUS_FINISHED)->count(),
            ],
            'filters'     => [
                'status'        => $status,
                'search'        => $search,
                'jenis_surat_id' => $jenisSuratId > 0 ? (string) $jenisSuratId : '',
            ],
            'jenisSurats' => JenisSurat::query()->orderBy('nama')->get(['id', 'nama'])
                ->map(fn (JenisSurat $j): array => ['id' => $j->id, 'nama' => $j->nama])
                ->values(),
        ]);
    }

    public function show(int $id): JsonResponse
    {
        $surat    = Surat::query()->with(['pemohon', 'jenisSurat', 'lampirans'])->findOrFail($id);
        $isiSurat = json_decode((string) $surat->isi_surat, true);

        return response()->json([
            'id'                    => $surat->id,
            'pemohon'               => [
                'name' => $surat->pemohon?->name,
                'nim'  => $surat->pemohon?->nim_nip ?? $surat->pemohon?->nomor_induk,
            ],
            'jenis_surat'           => $surat->jenisSurat?->nama,
            'keperluan'             => $surat->keperluan,
            'isi_surat'             => is_array($isiSurat) ? $isiSurat : [],
            'lampiran'              => $surat->lampirans->map(fn ($l): array => [
                'id'   => $l->id,
                'name' => $l->nama_file,
                'url'  => route('admin.lampiran.preview', $l->id, absolute: false),
                'type' => $l->tipe,
            ])->values(),
            'tanggal_pengajuan'     => optional($surat->tanggal_pengajuan ?? $surat->created_at)?->toISOString(),
            'status'                => $surat->status,
            'previewTemplateUrl'    => route('admin.surat.template-preview', $surat->id, absolute: false),
            'generatedDocumentUrl'  => filled($surat->generated_file_path)
                ? route('admin.surat.generated-document', $surat->id, absolute: false)
                : null,
        ]);
    }

    public function previewTemplate(int $id): HttpResponse
    {
        $surat    = Surat::query()->with(['pemohon', 'jenisSurat.template.placeholders', 'dataEntries'])->findOrFail($id);
        $rendered = $this->templateRenderer->renderForSurat($surat);

        return response(
            $this->templateRenderer->wrapDocumentHtml('Preview '.$surat->jenisSurat?->nama, $rendered['html']),
            200,
        )->header('Content-Type', 'text/html; charset=UTF-8');
    }

    public function previewGeneratedDocument(int $id): StreamedResponse
    {
        $surat = Surat::query()->findOrFail($id);
        abort_unless(filled($surat->generated_file_path) && Storage::disk('public')->exists($surat->generated_file_path), 404);

        $filename = sprintf('%s-%d.pdf', str_replace(' ', '-', strtolower((string) ($surat->jenisSurat?->nama ?: 'surat'))), $surat->id);

        return Storage::disk('public')->response($surat->generated_file_path, $filename, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'inline; filename="'.addslashes($filename).'"',
        ]);
    }

    public function previewAttachment(int $id): StreamedResponse
    {
        $lampiran = SuratLampiran::query()->findOrFail($id);
        abort_unless(Storage::disk('public')->exists($lampiran->file_path), 404);

        return Storage::disk('public')->response($lampiran->file_path, $lampiran->nama_file, [
            'Content-Type'        => $lampiran->tipe ?: 'application/octet-stream',
            'Content-Disposition' => 'inline; filename="'.addslashes($lampiran->nama_file).'"',
        ]);
    }

    public function approve(int $id): RedirectResponse
    {
        $surat = Surat::query()->findOrFail($id);
        $this->workflow->approve($surat, FastApprovalWorkflowService::ROLE_ADMIN, request()->user());

        return back()->with('success', 'Pengajuan berhasil divalidasi admin.');
    }

    public function reject(Request $request, int $id): RedirectResponse
    {
        $request->validate(['reason' => ['required', 'string']]);
        $surat = Surat::query()->findOrFail($id);
        $this->workflow->reject($surat, FastApprovalWorkflowService::ROLE_ADMIN, $request->user(), $request->string('reason')->toString());

        return back()->with('success', 'Pengajuan berhasil ditolak.');
    }
}
