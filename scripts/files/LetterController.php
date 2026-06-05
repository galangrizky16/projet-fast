<?php
// File: app/Http/Controllers/Admin/LetterController.php
// Menggantikan: app/Http/Controllers/Fast/Administrasi/AdminSuratController.php
// PERUBAHAN: namespace, class name, Inertia render path

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\JenisSurat;
use App\Models\Surat;
use App\Services\SuratTemplateRendererService;
use App\Services\SuratWorkflowService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class LetterController extends Controller
{
    public function __construct(
        protected SuratWorkflowService $workflow,
        protected SuratTemplateRendererService $templateService,
    ) {}

    public function create(): Response
    {
        $jenisSurats = JenisSurat::query()
            ->with(['category', 'template'])
            ->where('is_active', true)
            ->whereHas('template')
            ->orderBy('nama')
            ->get();

        // ── Render path diupdate ──
        return Inertia::render('admin/letters/Create', [
            'jenisSurats' => $jenisSurats->map(fn (JenisSurat $j): array => [
                'id'             => $j->id,
                'nama'           => $j->nama,
                'slug'           => $j->slug,
                'kode_surat'     => $j->kode_surat,
                'deskripsi'      => $j->deskripsi,
                'perlu_approval' => $j->perlu_approval,
                'category'       => ['id' => $j->category?->id, 'nama' => $j->category?->nama],
                'template'       => $j->template ? ['id' => $j->template->id, 'name' => $j->template->name] : null,
            ])->values(),
        ]);
    }

    public function selectType(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'jenis_surat_id' => [
                'required', 'integer',
                Rule::exists('jenis_surats', 'id')->where(fn ($q) => $q->where('is_active', true)),
            ],
        ]);

        return redirect()->route('admin.surat.form', $validated['jenis_surat_id']);
    }

    public function form(JenisSurat $jenisSurat): Response
    {
        $jenisSurat->loadMissing(['category', 'template.placeholders', 'approvalRole']);
        abort_if(!$jenisSurat->is_active || $jenisSurat->template === null, 404);

        // ── Render path diupdate ──
        return Inertia::render('admin/letters/Form', [
            'jenisSurat' => $this->serializeJenisSurat($jenisSurat),
            'formData'   => [
                'jenis_surat_id' => $jenisSurat->id,
                'keperluan'      => '',
                'data'           => $this->initialDynamicData($jenisSurat),
            ],
        ]);
    }

    public function preview(Request $request): RedirectResponse
    {
        [$jenisSurat, $payload] = $this->validatedPayload($request);
        $request->session()->put('admin_surat_preview', [
            'jenisSuratId' => $jenisSurat->id,
            'payload'      => $payload,
        ]);

        return redirect()->route('admin.surat.preview-page');
    }

    public function previewPage(Request $request): Response
    {
        $previewState = $request->session()->get('admin_surat_preview');
        abort_if(!is_array($previewState), 404, 'Data preview surat tidak ditemukan.');

        $jenisSurat = JenisSurat::query()
            ->with(['category', 'template.placeholders', 'approvalRole'])
            ->where('is_active', true)
            ->findOrFail((int) ($previewState['jenisSuratId'] ?? 0));

        $payload = $previewState['payload'] ?? [];
        abort_if(!is_array($payload), 404);

        $rendered = $this->templateService->renderJenisSuratPreview(
            $jenisSurat,
            $payload['data'],
            [
                'tanggal_surat' => now(),
                'kota_surat'    => 'Cilacap',
                'surat'         => ['nomor_surat' => 'AUTO/GENERATED', 'keperluan' => $payload['keperluan']],
                'user'          => ['name' => $request->user()?->name, 'email' => $request->user()?->email],
            ],
        );

        // ── Render path diupdate ──
        return Inertia::render('admin/letters/Preview', [
            'jenisSurat'          => $this->serializeJenisSurat($jenisSurat),
            'formData'            => $payload,
            'renderedHtml'        => $rendered['html'],
            'previewDocumentHtml' => $this->templateService->wrapDocumentHtml('Preview '.$jenisSurat->nama, $rendered['html']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        [, $payload] = $this->validatedPayload($request);
        $user = $request->user();
        abort_if($user === null, 403);

        $surat = $this->workflow->createOutgoing($user, $payload);

        return redirect()->route('admin.dashboard')
            ->with('success', 'Surat keluar berhasil dibuat.')
            ->with('generated_surat_id', $surat->id);
    }

    // ── Private helpers (sama persis dengan AdminSuratController) ──────────

    protected function validatedPayload(Request $request): array
    {
        $validated = $request->validate([
            'jenis_surat_id' => ['required', 'integer', Rule::exists('jenis_surats', 'id')->where(fn ($q) => $q->where('is_active', true))],
            'keperluan'      => ['required', 'string', 'max:255'],
            'data'           => ['required', 'array'],
        ]);

        $jenisSurat = JenisSurat::query()
            ->with(['category', 'template.placeholders', 'approvalRole'])
            ->where('is_active', true)
            ->findOrFail((int) $validated['jenis_surat_id']);

        abort_if($jenisSurat->template === null, 404, 'Template surat belum tersedia.');
        $validated['data'] = $this->workflow->validateDynamicData($jenisSurat, $validated['data']);

        return [$jenisSurat, $validated];
    }

    protected function serializeJenisSurat(JenisSurat $jenisSurat): array
    {
        return [
            'id'            => $jenisSurat->id,
            'nama'          => $jenisSurat->nama,
            'slug'          => $jenisSurat->slug,
            'deskripsi'     => $jenisSurat->deskripsi,
            'approval_role' => [
                'id'   => $jenisSurat->approvalRole?->id,
                'nama' => $jenisSurat->approvalRole?->nama,
                'slug' => $jenisSurat->approvalRole?->slug,
            ],
            'category'      => ['id' => $jenisSurat->category?->id, 'nama' => $jenisSurat->category?->nama],
            'template'      => ['id' => $jenisSurat->template?->id, 'name' => $jenisSurat->template?->name, 'subject' => $jenisSurat->template?->subject],
            'field_config'  => collect($jenisSurat->field_config ?? [])
                ->filter(fn ($f): bool => is_array($f) && filled($f['name'] ?? null))
                ->map(function (array $field): array {
                    $options = collect($field['options'] ?? [])
                        ->map(fn ($o): array => is_array($o)
                            ? ['label' => (string) ($o['label'] ?? $o['value'] ?? ''), 'value' => (string) ($o['value'] ?? '')]
                            : ['label' => (string) $o, 'value' => (string) $o]
                        )
                        ->filter(fn ($o): bool => $o['value'] !== '')
                        ->values()->all();

                    return [
                        'name'        => (string) $field['name'],
                        'label'       => (string) ($field['label'] ?? $field['name']),
                        'type'        => strtolower((string) ($field['type'] ?? 'text')),
                        'required'    => (bool) ($field['required'] ?? false),
                        'placeholder' => (string) ($field['placeholder'] ?? ''),
                        'help'        => (string) ($field['help'] ?? ''),
                        'options'     => $options,
                        'repeatable'  => (bool) ($field['repeatable'] ?? false) || strtolower((string) ($field['type'] ?? '')) === 'repeatable',
                        'add_label'   => (string) ($field['add_label'] ?? 'Tambah'),
                        'item_label'  => (string) ($field['item_label'] ?? 'Item'),
                    ];
                })
                ->values()->all(),
        ];
    }

    protected function initialDynamicData(JenisSurat $jenisSurat): array
    {
        return collect($jenisSurat->field_config ?? [])
            ->filter(fn ($f): bool => is_array($f) && filled($f['name'] ?? null))
            ->mapWithKeys(function (array $field): array {
                $type = strtolower((string) ($field['type'] ?? 'text'));
                if ($type === 'repeatable') return [(string) $field['name'] => ['']];
                if (in_array($type, ['checkbox-group', 'multiselect'], true)) return [(string) $field['name'] => []];
                if ($type === 'checkbox') return [(string) $field['name'] => false];
                return [(string) $field['name'] => ''];
            })
            ->all();
    }
}
