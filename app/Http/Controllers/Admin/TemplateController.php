<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\JenisSurat;
use App\Models\SuratTemplate;
use App\Models\SuratCategory;
use App\Models\TemplateGlobalSetting;
use App\Models\Role;
use App\Services\SuratTemplateRendererService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response as HttpResponse;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class TemplateController extends Controller
{
    public function __construct(
        protected SuratTemplateRendererService $templateRenderer,
    ) {
    }

    // ── Index ─────────────────────────────────────────────────────────────────
    public function index(Request $request): Response
    {
        $jenisSurats = JenisSurat::query()
            ->with(['category', 'template'])
            ->orderBy('nama')
            ->get();

        // Hanya pilih jika ada jenis_surat_id di query string — tidak auto-select
        $selectedId         = (int) $request->integer('jenis_surat_id');
        $selectedJenisSurat = $selectedId > 0
            ? $jenisSurats->firstWhere('id', $selectedId)
            : null;

        return Inertia::render('admin/templates/Index', [
            'jenisSurats' => $jenisSurats->map(fn (JenisSurat $j): array => [
                'id'        => $j->id,
                'nama'      => $j->nama,
                'slug'      => $j->slug,
                'is_active' => $j->is_active,
                'category'  => [
                    'id'   => $j->category?->id,
                    'nama' => $j->category?->nama,
                ],
                'template' => $j->template ? [
                    'id'         => $j->template->id,
                    'name'       => $j->template->name,
                    'version'    => $j->template->version,
                    'created_at' => optional($j->template->created_at)?->toISOString(),
                    'updated_at' => optional($j->template->updated_at)?->toISOString(),
                ] : null,
            ])->values(),
            'selectedJenisSurat'   => $selectedJenisSurat ? $this->serializeJenisSurat($selectedJenisSurat) : null,
            'selectedJenisSuratId' => $selectedJenisSurat?->id,
            'categories'           => SuratCategory::orderBy('urutan')->get(['id', 'nama']),
            'roles'                => Role::orderBy('nama')->get(['id', 'nama', 'slug']),
            'globalSettings'       => TemplateGlobalSetting::orderBy('id')
                                        ->get(['key', 'label', 'value', 'tipe'])
                                        ->toArray(),
        ]);
    }

    // ── Store ─────────────────────────────────────────────────────────────────
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nama'             => ['required', 'string', 'max:255'],
            'kode_surat'       => ['nullable', 'string', 'max:50'],
            'category_id'      => ['nullable', 'exists:surat_categories,id'],
            'deskripsi'        => ['nullable', 'string'],
            'allowed_role_id'  => ['nullable', 'exists:roles,id'],
            'approval_role_id' => ['nullable', 'exists:roles,id'],
            'perlu_approval'   => ['nullable', 'boolean'],
            'is_active'        => ['nullable', 'boolean'],
        ]);

        $jenisSurat = JenisSurat::create([
            'nama'             => $validated['nama'],
            'slug'             => Str::slug($validated['nama']) . '-' . time(),
            'kode_surat'       => $validated['kode_surat'] ?? null,
            'category_id'      => $validated['category_id'] ?? null,
            'deskripsi'        => $validated['deskripsi'] ?? null,
            'allowed_role_id'  => $validated['allowed_role_id'] ?? null,
            'approval_role_id' => $validated['approval_role_id'] ?? null,
            'perlu_approval'   => $request->boolean('perlu_approval', false),
            'is_active'        => $request->boolean('is_active', true),
            'alur_pengajuan'   => 'submission',
            'field_config'     => [],
        ]);

        return redirect()
            ->route('admin.templates.index', ['jenis_surat_id' => $jenisSurat->id])
            ->with('success', "Jenis surat \"{$jenisSurat->nama}\" berhasil ditambahkan.");
    }

    // ── Update ────────────────────────────────────────────────────────────────
    public function update(Request $request, JenisSurat $jenisSurat): RedirectResponse
    {
        $request->validate([
            'template_body'           => ['required', 'string'],
            'name'                    => ['nullable', 'string', 'max:255'],
            'field_config'            => ['nullable', 'array'],
            'field_config.*.name'     => ['nullable', 'string'],
            'field_config.*.label'    => ['nullable', 'string'],
            'field_config.*.type'     => ['nullable', 'string'],
            'field_config.*.required' => ['nullable', 'boolean'],
            'layout'                  => ['nullable', 'array'],
        ]);

        // ── Update meta jenis surat ────────────────────────────────────────
        $meta = [];
        if ($request->has('kode_surat'))      $meta['kode_surat']       = $request->input('kode_surat');
        if ($request->has('category_id'))     $meta['category_id']      = $request->input('category_id') ?: null;
        if ($request->has('approval_role_id'))$meta['approval_role_id'] = $request->input('approval_role_id') ?: null;
        if ($request->has('allowed_role_id')) $meta['allowed_role_id']  = $request->input('allowed_role_id') ?: null;
        if ($request->has('perlu_approval'))  $meta['perlu_approval']   = $request->boolean('perlu_approval');
        if ($request->has('is_active'))       $meta['is_active']        = $request->boolean('is_active');

        // ── Simpan field_config ────────────────────────────────────────────
        if ($request->has('field_config')) {
            $fieldConfig = collect($request->input('field_config', []))
                ->filter(fn ($f) => is_array($f) && !empty(trim($f['name'] ?? '')) && !empty(trim($f['label'] ?? '')))
                ->map(fn ($f) => [
                    'name'        => trim((string) ($f['name'] ?? '')),
                    'label'       => trim((string) ($f['label'] ?? '')),
                    'type'        => (string) ($f['type'] ?? 'text'),
                    'required'    => (bool) ($f['required'] ?? false),
                    'placeholder' => (string) ($f['placeholder'] ?? ''),
                    'help'        => (string) ($f['help'] ?? ''),
                    'options'     => $f['options'] ?? [],
                ])
                ->values()
                ->all();

            $meta['field_config'] = $fieldConfig;
        }

        if (!empty($meta)) {
            $jenisSurat->update($meta);
        }

        // ── Simpan template body ───────────────────────────────────────────
        $templateName   = trim($request->input('name', '')) ?: $jenisSurat->nama;
        $templateBody   = $request->input('template_body');
        $activeTemplate = $jenisSurat->template()->first();

        if ($activeTemplate === null) {
            $nextVersion = (int) SuratTemplate::query()
                ->where('jenis_surat_id', $jenisSurat->id)
                ->max('version') + 1;

            SuratTemplate::query()->create([
                'jenis_surat_id'  => $jenisSurat->id,
                'name'            => $templateName,
                'slug'            => sprintf('template-%s-v%d', $jenisSurat->slug ?: Str::slug($jenisSurat->nama), $nextVersion),
                'format'          => 'html',
                'template_header' => $request->input('template_header'),
                'template_body'   => $templateBody,
                'template_footer' => $request->input('template_footer'),
                'version'         => max(1, $nextVersion),
                'is_active'       => true,
            ]);
        } else {
            $activeTemplate->name            = $templateName;
            $activeTemplate->template_header = $request->input('template_header');
            $activeTemplate->template_body   = $templateBody;
            $activeTemplate->template_footer = $request->input('template_footer');

            if ($activeTemplate->isDirty(['template_body', 'template_header', 'template_footer'])) {
                $activeTemplate->version = (int) $activeTemplate->version + 1;
            }

            $activeTemplate->save();
        }

        // ── Simpan layout (margin & indent) ke css_style
        if ($request->has('layout')) {
            $layout = $request->input('layout', []);
            $css = [];

            $marginTop    = trim((string) ($layout['margin_top']    ?? ''));
            $marginRight  = trim((string) ($layout['margin_right']  ?? ''));
            $marginBottom = trim((string) ($layout['margin_bottom'] ?? ''));
            $marginLeft   = trim((string) ($layout['margin_left']   ?? ''));

            if ($marginTop || $marginRight || $marginBottom || $marginLeft) {
                $mt = $marginTop    ?: '12mm';
                $mr = $marginRight  ?: '15mm';
                $mb = $marginBottom ?: '25mm';
                $ml = $marginLeft   ?: '15mm';
                $css[] = "@page { margin: {$mt} {$mr} {$mb} {$ml}; size: A4 portrait; }";
            }

            $bodyIndent = (int) ($layout['body_indent'] ?? 0);
            if ($bodyIndent > 0) {
                $css[] = ".surat-content { padding-left: {$bodyIndent}px; padding-right: {$bodyIndent}px; }";
            }

            $paraIndent = (int) ($layout['paragraph_indent'] ?? 0);
            if ($paraIndent > 0) {
                $css[] = ".surat-paragraf { text-indent: {$paraIndent}px; }";
            }

            $tableIndent = (int) ($layout['table_indent'] ?? 0);
            if ($tableIndent > 0) {
                $css[] = ".surat-tabel { margin-left: {$tableIndent}px; }";
            }

            // $jenisSurat->template?->update(['css_style' => implode("", $css)]);
            $jenisSurat->template?->update(['css_style' => implode("\n", $css)]);
        }

        return to_route('admin.templates.index', ['jenis_surat_id' => $jenisSurat->id])
            ->with('success', 'Template surat berhasil disimpan.');
    }

    // ── Preview ───────────────────────────────────────────────────────────────
    public function preview(JenisSurat $jenisSurat): HttpResponse
    {
        $template = $jenisSurat->template()->with('placeholders')->firstOrFail();
        $rendered = $this->templateRenderer->renderTemplatePreview($template);

        return response(
            $this->templateRenderer->wrapDocumentHtml('Preview Template ' . $jenisSurat->nama, $rendered['html'], $template),
            200,
        )->header('Content-Type', 'text/html; charset=UTF-8');
    }

    // ── Destroy ───────────────────────────────────────────────────────────────
    public function destroy(JenisSurat $jenisSurat): RedirectResponse
    {
        $nama = $jenisSurat->nama;
        $jenisSurat->delete();

        return redirect()
            ->route('admin.templates.index')
            ->with('success', "Jenis surat \"{$nama}\" berhasil dihapus.");
    }

    // ── Toggle aktif/nonaktif ─────────────────────────────────────────────────
    public function toggleActive(JenisSurat $jenisSurat): RedirectResponse
    {
        $jenisSurat->update(['is_active' => !$jenisSurat->is_active]);
        $status = $jenisSurat->is_active ? 'diaktifkan' : 'dinonaktifkan';

        return redirect()
            ->route('admin.templates.index', ['jenis_surat_id' => $jenisSurat->id])
            ->with('success', "Jenis surat \"{$jenisSurat->nama}\" berhasil {$status}.");
    }

    // ── Duplikat ──────────────────────────────────────────────────────────────
    public function duplicate(JenisSurat $jenisSurat): RedirectResponse
    {
        $baru             = $jenisSurat->replicate();
        $baru->nama       = $jenisSurat->nama . ' (Salinan)';
        $baru->slug       = Str::slug($baru->nama) . '-' . time();
        $baru->kode_surat = $jenisSurat->kode_surat ? $jenisSurat->kode_surat . '-COPY' : null;
        $baru->is_active  = false;
        $baru->save();

        if ($jenisSurat->template) {
            $templateBaru                 = $jenisSurat->template->replicate();
            $templateBaru->jenis_surat_id = $baru->id;
            $templateBaru->slug           = Str::slug($baru->nama) . '-tmpl-' . time();
            $templateBaru->is_active      = false;
            $templateBaru->version        = 1;
            $templateBaru->save();
        }

        return redirect()
            ->route('admin.templates.index', ['jenis_surat_id' => $baru->id])
            ->with('success', "Jenis surat \"{$baru->nama}\" berhasil diduplikat.");
    }

    // ── Serialize ─────────────────────────────────────────────────────────────
    protected function serializeJenisSurat(JenisSurat $jenisSurat): array
    {
        $jenisSurat->loadMissing(['category', 'template.placeholders', 'approvalRole', 'allowedRole']);

        return [
            'id'             => $jenisSurat->id,
            'nama'           => $jenisSurat->nama,
            'slug'           => $jenisSurat->slug,
            'kode_surat'     => $jenisSurat->kode_surat,
            'deskripsi'      => $jenisSurat->deskripsi,
            'is_active'      => $jenisSurat->is_active,
            'perlu_approval' => $jenisSurat->perlu_approval,
            'created_at'     => optional($jenisSurat->created_at)?->toISOString(),
            'updated_at'     => optional($jenisSurat->updated_at)?->toISOString(),
            'category' => [
                'id'   => $jenisSurat->category?->id,
                'nama' => $jenisSurat->category?->nama,
            ],
            'approval_role' => [
                'id'   => $jenisSurat->approvalRole?->id,
                'nama' => $jenisSurat->approvalRole?->nama,
                'slug' => $jenisSurat->approvalRole?->slug,
            ],
            'allowed_role' => [
                'id'   => $jenisSurat->allowedRole?->id,
                'nama' => $jenisSurat->allowedRole?->nama,
            ],
            'field_config' => collect($jenisSurat->field_config ?? [])
                ->filter(fn ($f): bool => is_array($f) && filled($f['name'] ?? null))
                ->map(fn (array $f): array => [
                    'name'        => (string) $f['name'],
                    'label'       => (string) ($f['label'] ?? $f['name']),
                    'type'        => strtolower((string) ($f['type'] ?? 'text')),
                    'required'    => (bool) ($f['required'] ?? false),
                    'placeholder' => (string) ($f['placeholder'] ?? ''),
                    'help'        => (string) ($f['help'] ?? ''),
                    'options'     => collect($f['options'] ?? [])
                        ->map(fn ($o): array => is_array($o)
                            ? ['label' => (string) ($o['label'] ?? $o['value'] ?? ''), 'value' => (string) ($o['value'] ?? '')]
                            : ['label' => (string) $o, 'value' => (string) $o]
                        )
                        ->filter(fn ($o): bool => $o['value'] !== '')
                        ->values()->all(),
                    'repeatable' => (bool) ($f['repeatable'] ?? false),
                    'add_label'  => (string) ($f['add_label'] ?? 'Tambah'),
                    'item_label' => (string) ($f['item_label'] ?? 'Item'),
                ])
                ->values()
                ->all(),
            'template' => $jenisSurat->template ? [
                'id'               => $jenisSurat->template->id,
                'name'             => $jenisSurat->template->name,
                'source_reference' => $jenisSurat->template->source_reference,
                'subject'          => $jenisSurat->template->subject,
                'template_header'  => $jenisSurat->template->template_header,
                'template_body'    => $jenisSurat->template->template_body,
                'template_footer'  => $jenisSurat->template->template_footer,
                'version'          => $jenisSurat->template->version,
                'css_style'        => $jenisSurat->template->css_style,
                'created_at'       => optional($jenisSurat->template->created_at)?->toISOString(),
                'updated_at'       => optional($jenisSurat->template->updated_at)?->toISOString(),
                'preview_url'      => route('admin.templates.preview', $jenisSurat->id, absolute: false),
                'placeholders'     => $jenisSurat->template->placeholders->map(fn ($p): array => [
                    'key'           => $p->placeholder_key,
                    'label'         => $p->label,
                    'source_type'   => $p->source_type,
                    'source_key'    => $p->source_key,
                    'is_required'   => $p->is_required,
                    'default_value' => $p->default_value,
                ])->values(),
            ] : null,
        ];
    }
}