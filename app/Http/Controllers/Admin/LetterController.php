<?php

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
    ) {
    }

    public function create(): Response
    {
        $jenisSurats = JenisSurat::query()
            ->with(['category', 'template'])
            ->where('is_active', true)
            ->whereHas('template')
            ->orderBy('nama')
            ->get();

        return Inertia::render('admin/letters/Create', [
            'jenisSurats' => $jenisSurats->map(fn (JenisSurat $jenisSurat): array => [
                'id'       => $jenisSurat->id,
                'nama'     => $jenisSurat->nama,
                'slug'     => $jenisSurat->slug,
                'deskripsi'=> $jenisSurat->deskripsi,
                'category' => [
                    'id'   => $jenisSurat->category?->id,
                    'nama' => $jenisSurat->category?->nama,
                ],
                'template' => [
                    'id'   => $jenisSurat->template?->id,
                    'name' => $jenisSurat->template?->name,
                ],
            ])->values(),
            'categories' => \App\Models\SuratCategory::orderBy('urutan')
                                ->get(['id', 'nama']),
        ]);
    }

    public function selectType(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'jenis_surat_id' => [
                'required',
                'integer',
                Rule::exists('jenis_surats', 'id')->where(fn ($query) => $query->where('is_active', true)),
            ],
        ]);

        return redirect()->route('admin.surat.form', $validated['jenis_surat_id']);
    }

    public function form(JenisSurat $jenisSurat): Response
    {
        $jenisSurat->loadMissing(['category', 'template.placeholders', 'approvalRole']);

        abort_if(! $jenisSurat->is_active || $jenisSurat->template === null, 404);

        return Inertia::render('admin/letters/Form', [
            'jenisSurat' => $this->serializeJenisSurat($jenisSurat),
            'formData' => [
                'jenis_surat_id' => $jenisSurat->id,
                'keperluan' => '',
                'data' => $this->initialDynamicData($jenisSurat),
            ],
        ]);
    }

    public function preview(Request $request): RedirectResponse
    {
        [$jenisSurat, $payload] = $this->validatedPayload($request);

        $request->session()->put('admin_surat_preview', [
            'jenisSuratId' => $jenisSurat->id,
            'payload' => $payload,
        ]);

        return redirect()->route('admin.surat.preview-page');
    }

    public function previewPage(Request $request): Response
    {
        $previewState = $request->session()->get('admin_surat_preview');

        abort_if(! is_array($previewState), 404, 'Data preview surat tidak ditemukan.');

        $jenisSurat = JenisSurat::query()
            ->with(['category', 'template.placeholders', 'approvalRole'])
            ->where('is_active', true)
            ->findOrFail((int) ($previewState['jenisSuratId'] ?? 0));

        $payload = $previewState['payload'] ?? [];
        abort_if(! is_array($payload), 404, 'Payload preview surat tidak valid.');

        $rendered = $this->templateService->renderJenisSuratPreview(
            $jenisSurat,
            $payload['data'],
            [
                'tanggal_surat' => now(),
                'kota_surat' => \DB::table('template_global_settings')->where('key', 'kota_surat')->value('value') ?? 'Cilacap',    
                'surat' => [
                    'nomor_surat' => 'AUTO/GENERATED/AFTER/APPROVAL',
                    'keperluan' => $payload['keperluan'],
                ],
                'user' => [
                    'name' => $request->user()?->name,
                    'email' => $request->user()?->email,
                ],
            ],
        );

        return Inertia::render('admin/letters/Preview', [
            'jenisSurat' => $this->serializeJenisSurat($jenisSurat),
            'formData' => $payload,
            'renderedHtml' => $rendered['html'],
            'previewDocumentHtml' => $this->templateService->wrapDocumentHtml(
                'Preview '.$jenisSurat->nama,
                $rendered['html'],
                $jenisSurat->template,
            ),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        [, $payload] = $this->validatedPayload($request);
        $user = $request->user();

        abort_if($user === null, 403);

        $surat = $this->workflow->createOutgoing($user, $payload);

        if ($surat->status === Surat::STATUS_FINISHED) {
            return redirect()
                ->route('admin.dashboard')
                ->with('success', 'Surat berhasil dibuat dan PDF langsung digenerate.')
                ->with('generated_surat_id', $surat->id);
        }

        return redirect()
            ->route('admin.dashboard')
            ->with('success', 'Surat berhasil dibuat dan diteruskan ke '.($surat->finalApprovalRoleSlug() === 'dekan' ? 'Dekan' : 'Kaprodi').' untuk persetujuan.');
    }

    public function generate(Request $request, int $id): RedirectResponse
    {
        $user = $request->user();

        abort_if($user === null, 403);

        $surat = Surat::query()->findOrFail($id);

        abort_if($surat->type !== 'surat_keluar', 404);

        $generated = $this->workflow->generateDocument($surat, $user, true);

        return redirect()
            ->route('admin.surat.generated-document', $generated->id)
            ->with('success', 'PDF surat berhasil digenerate.');
    }

    public function edit(int $id): Response
    {
        $surat = Surat::query()
            ->with(['jenisSurat.category', 'jenisSurat.template', 'jenisSurat.approvalRole', 'dataEntries'])
            ->findOrFail($id);

        abort_unless($surat->status === Surat::STATUS_REJECTED, 403);
        abort_unless(auth()->user()?->hasRole('admin'), 403);

        $jenisSurat = $surat->jenisSurat;

        return Inertia::render('admin/letters/Edit', [
            'surat' => [
                'id' => $surat->id,
                'keperluan' => $surat->keperluan,
            ],
            'jenisSurat' => $this->serializeJenisSurat($jenisSurat),
            'formData' => [
                'jenis_surat_id' => $jenisSurat->id,
                'keperluan' => $surat->keperluan,
                'data' => $this->workflow->extractExistingData($surat),
            ],
        ]);
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $surat = Surat::query()->findOrFail($id);
        $user = $request->user();

        abort_if($user === null, 403);

        [, $payload] = $this->validatedPayload($request);

        $this->workflow->editRejected($surat, $user, $payload);

        return redirect()
            ->route('admin.dashboard')
            ->with('success', 'Surat berhasil diperbarui dan diteruskan kembali untuk persetujuan.');
    }

    /**
     * @return array{0: JenisSurat, 1: array{jenis_surat_id: int, keperluan: string, data: array<string, mixed>}}
     */
    protected function validatedPayload(Request $request): array
    {
        $validated = $request->validate([
            'jenis_surat_id' => [
                'required',
                'integer',
                Rule::exists('jenis_surats', 'id')->where(fn ($query) => $query->where('is_active', true)),
            ],
            'keperluan' => ['required', 'string', 'max:255'],
            'form_data' => ['nullable', 'array'],
        ]);

        $jenisSurat = JenisSurat::query()
            ->with(['category', 'template.placeholders', 'approvalRole'])
            ->where('is_active', true)
            ->findOrFail((int) $validated['jenis_surat_id']);

        abort_if($jenisSurat->template === null, 404, 'Template surat belum tersedia.');

        $validated['data'] = $this->workflow->validateDynamicData($jenisSurat, $validated['form_data'] ?? []);

        return [$jenisSurat, $validated];
    }

    /**
     * @return array<string, mixed>
     */
    protected function serializeJenisSurat(JenisSurat $jenisSurat): array
    {
        return [
            'id' => $jenisSurat->id,
            'nama' => $jenisSurat->nama,
            'slug' => $jenisSurat->slug,
            'deskripsi' => $jenisSurat->deskripsi,
            'approval_role' => [
                'id' => $jenisSurat->approvalRole?->id,
                'nama' => $jenisSurat->approvalRole?->nama,
                'slug' => $jenisSurat->approvalRole?->slug,
            ],
            'category' => [
                'id' => $jenisSurat->category?->id,
                'nama' => $jenisSurat->category?->nama,
            ],
            'template' => [
                'id' => $jenisSurat->template?->id,
                'name' => $jenisSurat->template?->name,
                'subject' => $jenisSurat->template?->subject,
            ],
            'field_config' => collect($jenisSurat->field_config ?? [])
                ->filter(fn ($field): bool => is_array($field) && filled($field['name'] ?? null))
                ->map(function (array $field): array {
                    $options = collect($field['options'] ?? [])
                        ->map(function ($option): array {
                            if (is_array($option)) {
                                $value = (string) ($option['value'] ?? '');

                                return [
                                    'label' => (string) ($option['label'] ?? $value),
                                    'value' => $value,
                                ];
                            }

                            return [
                                'label' => (string) $option,
                                'value' => (string) $option,
                            ];
                        })
                        ->filter(fn (array $option): bool => $option['value'] !== '')
                        ->values()
                        ->all();

                    return [
                        'name' => (string) $field['name'],
                        'label' => (string) ($field['label'] ?? $field['name']),
                        'type' => strtolower((string) ($field['type'] ?? 'text')),
                        'required' => (bool) ($field['required'] ?? false),
                        'placeholder' => (string) ($field['placeholder'] ?? ''),
                        'help' => (string) ($field['help'] ?? ''),
                        'options' => $options,
                        'repeatable' => (bool) ($field['repeatable'] ?? false) || strtolower((string) ($field['type'] ?? '')) === 'repeatable',
                        'add_label' => (string) ($field['add_label'] ?? 'Tambah'),
                        'item_label' => (string) ($field['item_label'] ?? 'Item'),
                    ];
                })
                ->values()
                ->all(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function initialDynamicData(JenisSurat $jenisSurat): array
    {
        return collect($jenisSurat->field_config ?? [])
            ->filter(fn ($field): bool => is_array($field) && filled($field['name'] ?? null))
            ->mapWithKeys(function (array $field): array {
                $type = strtolower((string) ($field['type'] ?? 'text'));

                if ($type === 'repeatable') {
                    return [(string) $field['name'] => ['']];
                }

                if (in_array($type, ['checkbox-group', 'multiselect'], true)) {
                    return [(string) $field['name'] => []];
                }

                if ($type === 'checkbox') {
                    return [(string) $field['name'] => false];
                }

                return [(string) $field['name'] => ''];
            })
            ->all();
    }
}
