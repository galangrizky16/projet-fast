<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\JenisSurat;
use App\Models\Surat;
use App\Models\SuratCategory;
use App\Models\SuratData;
use App\Models\SuratLampiran;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class SubmissionController extends Controller
{
    /**
     * Halaman "Ajukan Surat": pilih jenis surat → isi form → upload → submit.
     */
    public function create(Request $request): Response
    {
        $user = $request->user();
        abort_if($user === null, 403);
        $user->loadMissing('role');

        $roleId = $user->role?->id;

        $jenisSurats = JenisSurat::query()
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

        return Inertia::render('fast/user/Ajukan', [
            'categories' => $categories->map(fn (SuratCategory $c): array => [
                'id'        => $c->id,
                'nama'      => $c->nama,
                'slug'      => $c->slug,
                'deskripsi' => $c->deskripsi,
            ])->values(),
            'jenisSurats' => $jenisSurats->map(fn (JenisSurat $j): array => [
                'id'          => $j->id,
                'categoryId'  => $j->category_id,
                'nama'        => $j->nama,
                'slug'        => $j->slug,
                'deskripsi'   => $j->deskripsi,
                'fieldConfig' => collect($this->normalizeFieldConfig($j->field_config ?? []))
                    ->map(fn (array $f): array => [
                        'name'        => $f['name'],
                        'label'       => (string) $f['label'],
                        'type'        => $f['type'],
                        'required'    => (bool) $f['required'],
                        'placeholder' => (string) ($f['placeholder'] ?? ''),
                        'options'     => collect($f['options'] ?? [])
                            ->map(fn ($o) => is_array($o)
                                ? ['label' => (string) ($o['label'] ?? $o['value'] ?? ''), 'value' => (string) ($o['value'] ?? '')]
                                : ['label' => (string) $o, 'value' => (string) $o]
                            )->filter(fn ($o) => $o['value'] !== '')->values()->all(),
                    ])->values()->all(),
            ])->values(),
            'selectedJenisId' => $request->integer('jenis') ?: null,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();
        abort_if($user === null, 403);

        $validated = Validator::make($request->all(), [
            'jenis_surat_id' => ['required', 'integer', 'exists:jenis_surats,id'],
            'keperluan' => ['required', 'string', 'min:10', 'max:1000'],
            'tanggal_kebutuhan' => ['required', 'date', 'after_or_equal:today'],
            'field_data' => ['nullable', 'array'],
            'lampiran' => ['nullable', 'array'],
            'lampiran.*' => ['file', 'max:4096', 'mimes:pdf,jpg,jpeg,png,doc,docx'],
        ], [
            'jenis_surat_id.required'        => 'Jenis surat wajib dipilih.',
            'keperluan.required'             => 'Keperluan wajib diisi.',
            'keperluan.min'                  => 'Keperluan minimal 10 karakter.',
            'keperluan.max'                  => 'Keperluan maksimal 1000 karakter.',
            'tanggal_kebutuhan.required'     => 'Tanggal kebutuhan wajib diisi.',
            'tanggal_kebutuhan.date'         => 'Format tanggal kebutuhan tidak valid.',
            'tanggal_kebutuhan.after_or_equal' => 'Tanggal kebutuhan tidak boleh di masa lalu.',
        ])->validate();

        /** @var JenisSurat $jenisSurat */
        $jenisSurat = JenisSurat::query()->findOrFail($validated['jenis_surat_id']);

        $dynamicRules = [];
        $dynamicMessages = [];

        foreach ($this->normalizeFieldConfig($jenisSurat->field_config ?? []) as $field) {
            $key = 'field_data.'.$field['name'];
            $rules = [($field['required'] ?? false) ? 'required' : 'nullable'];

            $options = collect($field['options'] ?? [])
                ->map(fn ($option) => is_array($option) ? ($option['value'] ?? null) : $option)
                ->filter(fn ($option) => filled($option))
                ->values();

            switch ($field['type']) {
                case 'number':
                    $rules[] = 'numeric';
                    break;
                case 'date':
                    $rules[] = 'date';
                    break;
                case 'email':
                    $rules[] = 'email';
                    break;
                case 'checkbox':
                    $rules[] = 'boolean';
                    break;
                case 'checkbox-group':
                case 'multiselect':
                    $rules[] = 'array';
                    if ($options->isNotEmpty()) {
                        $dynamicRules[$key.'.*'] = [Rule::in($options->all())];
                    }
                    break;
                case 'select':
                case 'radio':
                    $rules[] = 'string';
                    if ($options->isNotEmpty()) {
                        $rules[] = Rule::in($options->all());
                    }
                    break;
                default:
                    $rules[] = 'string';
                    break;
            }

            $dynamicRules[$key] = $rules;
            $dynamicMessages[$key.'.required'] = ($field['label'] ?? $field['name']).' wajib diisi.';
        }

        Validator::make($request->all(), $dynamicRules, $dynamicMessages)->validate();

        $storedPaths = [];

        try {
            DB::transaction(function () use ($request, $validated, $jenisSurat, $user, &$storedPaths): void {
                $payloadData = Arr::wrap($request->input('field_data', []));

                $surat = Surat::query()->create([
                    'jenis_surat_id' => $jenisSurat->id,
                    'pemohon_id' => $user->id,
                    'keperluan' => $validated['keperluan'],
                    'status' => Surat::STATUS_PENDING,
                    'tanggal_pengajuan' => now(),
                    'tanggal_kebutuhan' => $validated['tanggal_kebutuhan'],
                    'isi_surat' => json_encode([
                        'jenis_surat_id' => $jenisSurat->id,
                        'jenis_surat' => $jenisSurat->nama,
                        'keperluan' => $validated['keperluan'],
                        'tanggal_kebutuhan' => $validated['tanggal_kebutuhan'],
                        'data' => $payloadData,
                    ], JSON_THROW_ON_ERROR),
                ]);

                foreach ($payloadData as $fieldName => $fieldValue) {
                    SuratData::query()->create([
                        'surat_id' => $surat->id,
                        'field_name' => (string) $fieldName,
                        'field_value' => is_array($fieldValue)
                            ? json_encode($fieldValue, JSON_THROW_ON_ERROR)
                            : (string) $fieldValue,
                    ]);
                }

                /** @var array<int, UploadedFile> $lampirans */
                $lampirans = $request->file('lampiran', []);

                foreach ($lampirans as $lampiran) {
                    $path = $lampiran->store('surat-lampirans', 'public');
                    $storedPaths[] = $path;

                    SuratLampiran::query()->create([
                        'surat_id' => $surat->id,
                        'nama_file' => $lampiran->getClientOriginalName(),
                        'file_path' => $path,
                        'tipe' => $lampiran->getClientMimeType(),
                    ]);
                }
            });
        } catch (\Throwable $throwable) {
            foreach ($storedPaths as $path) {
                Storage::disk('public')->delete($path);
            }

            throw $throwable;
        }

        return redirect()
            ->route('fast.user.dashboard')
            ->with('success', 'Pengajuan surat berhasil dikirim.');
    }

    /**
     * @param  array<int, mixed>  $fieldConfig
     * @return array<int, array<string, mixed>>
     */
    protected function normalizeFieldConfig(array $fieldConfig): array
    {
        return collect($fieldConfig)
            ->filter(fn ($field): bool => is_array($field) && filled($field['name'] ?? null))
            ->map(fn (array $field): array => [
                'name' => (string) $field['name'],
                'label' => $field['label'] ?? $field['name'],
                'type' => strtolower((string) ($field['type'] ?? 'text')),
                'required' => (bool) ($field['required'] ?? false),
                'options' => $field['options'] ?? [],
            ])
            ->values()
            ->all();
    }

}
