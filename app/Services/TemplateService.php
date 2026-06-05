<?php

namespace App\Services;

use App\Models\JenisSurat;
use App\Models\Surat;
use App\Models\SuratTemplate;
use Carbon\CarbonInterface;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class TemplateService
{
    protected const DOCUMENT_STYLES = <<<'CSS'
/* @page { */
    /* margin: 45mm 15mm 30mm 15mm;
    /* margin: 0; */
    /* size: A4 portrait; */ */
/* } */

body {
    background: #fff;
    color: #0f172a;
    font-family: "Cambria", "Times New Roman", serif;
    font-size: 12pt;
    margin: 0;
    padding: 0;
}

.page {
    box-sizing: border-box;
    min-height: 0;
    padding: 0;
}

h1 {
    font-size: 14pt;
    letter-spacing: 0.04em;
    margin-bottom: 8px;
    text-transform: uppercase;
}

p {
    font-size: 12pt;
    line-height: 1.5;
    margin: 0 0 10px;
}

ul,
ol {
    font-size: 12pt;
    line-height: 1.5;
    margin: 0 0 12px 20px;
}

table {
    font-size: 12pt;
    line-height: 1.5;
    width: 100%;
}

td,
th,
span,
div {
    font-size: inherit;
}
CSS;

    // ── Render untuk surat yang sudah ada (dengan QR) ──────────────────────

    /**
     * @return array{template: SuratTemplate, html: string, placeholders: array<string, mixed>}
     */
    public function renderForSurat(Surat $surat): array
    {
        $surat->loadMissing([
            'pemohon',
            'jenisSurat.template.placeholders',
            'dataEntries',
        ]);

        $template = $surat->jenisSurat?->template;
        abort_if($template === null, 404, 'Template surat belum tersedia untuk jenis surat ini.');

        $placeholderValues = $this->resolvePlaceholderValues($surat, $template);

        // Tentukan apakah QR aktif
        $suratFinished = $surat->status === 'finished'
            || (!($surat->jenisSurat->perlu_approval ?? true) && !empty($surat->qr_token));

        $rendered = $this->renderTemplate(
            $template,
            $placeholderValues,
            $surat->qr_token,
            $suratFinished
        );

        return [
            'template'     => $template,
            'html'         => $rendered['full'],
            'body'         => $rendered['body'],
            'footer'       => $rendered['footer'],
            'header'       => $rendered['header'],
            'placeholders' => $placeholderValues,
        ];
    }

    // ── Render preview saat buat surat (QR placeholder) ───────────────────

    /**
     * @return array{template: SuratTemplate, html: string, placeholders: array<string, mixed>}
     */
    public function renderJenisSuratPreview(JenisSurat $jenisSurat, array $data, array $context = []): array
    {
        $jenisSurat->loadMissing('template.placeholders');

        $template = $jenisSurat->template;
        abort_if($template === null, 404, 'Template surat belum tersedia untuk jenis surat ini.');

        $placeholderValues = $this->resolvePreviewPlaceholderValues($template, $data, $context);

        // Preview: QR tidak aktif — tampilkan placeholder
        $html = $this->renderTemplate($template, $placeholderValues, null, false);

        return [
            'template'     => $template,
            'html'         => $rendered['full'],
            'placeholders' => $placeholderValues,
        ];
    }

    // ── Render preview template (dari halaman admin templates) ─────────────

    /**
     * @return array{template: SuratTemplate, html: string, placeholders: array<string, string>}
     */
    public function renderTemplatePreview(SuratTemplate $template): array
    {
        $template->loadMissing('placeholders');

        $placeholderValues = $template->placeholders
            ->mapWithKeys(fn ($placeholder): array => [
                $placeholder->placeholder_key => $this->normalizePlaceholderValue(
                    $placeholder->placeholder_key,
                    match ($placeholder->source_type) {
                        'system' => $this->resolveSystemValue(
                            (string) $placeholder->source_key,
                            $placeholder->default_value
                        ),
                        default => $placeholder->default_value,
                    },
                    '[' . $placeholder->label . ']'
                ),
            ])
            ->all();

        // Preview admin: QR placeholder
        $html = $this->renderTemplate($template, $placeholderValues, null, false);

        return [
            'template'     => $template,
            'html'         => $rendered['full'],
            'placeholders' => $placeholderValues,
        ];
    }

    // ── Wrap HTML lengkap ──────────────────────────────────────────────────

    public function wrapDocumentHtml(string $title, string $html, ?SuratTemplate $template = null): string
    {
        $safeTitle = e($title);

        // Margin dari global settings
        $settings     = \App\Models\TemplateGlobalSetting::allAsArray();
        $marginTop    = $settings['margin_top']    ?? '12mm';
        $marginRight  = $settings['margin_right']  ?? '15mm';
        $marginBottom = $settings['margin_bottom'] ?? '25mm';
        $marginLeft   = $settings['margin_left']   ?? '15mm';

        $pageStyle  = "@page { margin: {$marginTop} {$marginRight} {$marginBottom} {$marginLeft}; size: A4 portrait; }";
        $styles     = $this->documentStyles();

        // CSS custom per template (margin, indent, dll)
        $customCss = $template?->css_style ? "
        /* Custom CSS per template */
        {$template->css_style}" : '';

                return <<<HTML
        <!DOCTYPE html>
        <html lang="id">
        <head>
            <meta charset="utf-8">
            <meta name="viewport" content="width=device-width, initial-scale=1">
            <title>{$safeTitle}</title>
            <style>
                {$pageStyle}
                {$styles}
                {$customCss}
            </style>
        </head>
        <body>
            <div class="page">
                {$html}
            </div>
        </body>
        </html>
        HTML;
    }

    public function documentStyles(): string
    {
        return self::DOCUMENT_STYLES;
    }

    // ── Core render template ───────────────────────────────────────────────

    /**
     * @param  array<string, mixed>  $placeholderValues
     */
    protected function renderTemplate(
        SuratTemplate $template,
        array $placeholderValues,
        ?string $qrToken = null,
        bool $suratFinished = false
    ): array {//:string
        $body     = (string) ($template->template_body ?? '');
        $settings = \App\Models\TemplateGlobalSetting::allAsArray();

        // ── Inject QR data ─────────────────────────────────────────────────
        $qrSvg    = '';
        $qrActive = false;

        if (!empty($qrToken)) {
            $qrActive = $suratFinished;
            if ($qrActive) {
                $verifyUrl = url('/verifikasi-qr/' . $qrToken);
                $qrSvg     = static::generateQrSvg($verifyUrl);
            }
        }

        $placeholderValues['__qr_svg']    = $qrSvg;
        $placeholderValues['__qr_active'] = $qrActive;

        // ── Render body ────────────────────────────────────────────────────
        if (\App\Services\SuratKomponenRenderer::isKomponenJson($body)) {
            $komponen     = json_decode($body, true) ?? [];
            $renderedBody = \App\Services\SuratKomponenRenderer::render($komponen, $placeholderValues);
        } else {
            $renderedBody = $this->replacePlaceholders($body, $placeholderValues);
        }
        $renderedBody = '<div class="surat-content">' . $renderedBody . '</div>';

        // ── Header & Footer otomatis ───────────────────────────────────────
        $header = !empty(trim((string) ($template->template_header ?? '')))
            ? $this->replacePlaceholders((string) $template->template_header, $placeholderValues)
            : \App\Services\SuratKomponenRenderer::renderKop($settings, $placeholderValues);

        $footer = !empty(trim((string) ($template->template_footer ?? '')))
            ? $this->replacePlaceholders((string) $template->template_footer, $placeholderValues)
            : \App\Services\SuratKomponenRenderer::renderFooter($settings);

        // return implode("\n", array_filter([$header, $renderedBody, $footer]));
        return [
            'header' => $header,
            'body'   => $renderedBody,
            'footer' => $footer,
            'full'   => implode("\n", array_filter([$header, $renderedBody, $footer])),
        ];
    }

    // ── Generate QR SVG ────────────────────────────────────────────────────

    protected static function generateQrSvg(string $content): string
    {
        try {
            $renderer = new \BaconQrCode\Renderer\ImageRenderer(
                new \BaconQrCode\Renderer\RendererStyle\RendererStyle(113),
                new \BaconQrCode\Renderer\Image\SvgImageBackEnd()
            );
            $writer = new \BaconQrCode\Writer($renderer);
            return $writer->writeString($content);
        } catch (\Throwable $e) {
            return '';
        }
    }

    // ── Resolve placeholder values ─────────────────────────────────────────

    /**
     * @return array<string, mixed>
     */
    public function resolvePlaceholderValues(Surat $surat, SuratTemplate $template): array
    {
        $suratData = $this->extractSuratData($surat);
        $payload   = [];

        foreach ($template->placeholders as $placeholder) {
            $key       = (string) $placeholder->placeholder_key;
            $sourceKey = (string) $placeholder->source_key;

            $value = match ($placeholder->source_type) {
                'surat'      => data_get($surat, $sourceKey),
                'user'       => data_get($surat->pemohon, $sourceKey),
                'surat_data' => Arr::get($suratData, $sourceKey),
                'computed'   => $this->resolveComputedValue($surat, $suratData, $sourceKey),
                'system'     => $this->resolveSystemValue($sourceKey, $placeholder->default_value),
                default      => $placeholder->default_value,
            };

            $payload[$key] = $this->normalizePlaceholderValue($key, $value, $placeholder->default_value);
        }

        return $payload;
    }

    /**
     * @param  array<string, mixed>  $data
     * @param  array<string, mixed>  $context
     * @return array<string, mixed>
     */
    public function resolvePreviewPlaceholderValues(SuratTemplate $template, array $data, array $context = []): array
    {
        $template->loadMissing('placeholders');

        $suratContext = Arr::get($context, 'surat', []);
        $userContext  = Arr::get($context, 'user', []);
        $payload      = [];

        foreach ($template->placeholders as $placeholder) {
            $key       = (string) $placeholder->placeholder_key;
            $sourceKey = (string) $placeholder->source_key;

            $value = match ($placeholder->source_type) {
                'surat'      => data_get($suratContext, $sourceKey, Arr::get($context, $sourceKey)),
                'user'       => data_get($userContext, $sourceKey, Arr::get($context, $sourceKey)),
                'surat_data' => Arr::get($data, $sourceKey),
                'computed'   => $this->resolveComputedPreviewValue($data, $context, $sourceKey),
                'system'     => Arr::get($context, $sourceKey, $this->resolveSystemValue($sourceKey, $placeholder->default_value)),
                default      => Arr::get($context, $sourceKey, $placeholder->default_value),
            };

            $payload[$key] = $this->normalizePlaceholderValue($key, $value, $placeholder->default_value);
        }

        return $payload;
    }

    // ── Extract surat data ─────────────────────────────────────────────────

    /**
     * @return array<string, mixed>
     */
    protected function extractSuratData(Surat $surat): array
    {
        $dataFromEntries = $surat->dataEntries
            ->mapWithKeys(function ($entry): array {
                $value   = $entry->field_value;
                $decoded = json_decode((string) $value, true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    $value = $decoded;
                }
                return [$entry->field_name => $value];
            })
            ->all();

        $isiSurat   = json_decode((string) $surat->isi_surat, true);
        $nestedData = is_array($isiSurat) ? Arr::get($isiSurat, 'data', []) : [];

        return array_replace(
            is_array($nestedData) ? $nestedData : [],
            $dataFromEntries,
        );
    }

    // ── Computed values ────────────────────────────────────────────────────

    protected function resolveComputedValue(Surat $surat, array $suratData, string $sourceKey): mixed
    {
        return match ($sourceKey) {
            'semester_terbilang'      => $this->numberToWords(Arr::get($suratData, 'semester')),
            'tanggal_surat_panjang'   => $this->formatDateLong($surat->tanggal_selesai ?? $surat->created_at ?? now()),
            'tanggal_yudisium_panjang'=> $this->formatDateLong(Arr::get($suratData, 'tanggal_yudisium')),
            'tanggal_mulai_panjang'   => $this->formatDateLong(Arr::get($suratData, 'tanggal_mulai')),
            'tanggal_selesai_panjang' => $this->formatDateLong(Arr::get($suratData, 'tanggal_selesai')),
            'tanggal_kegiatan_panjang'=> $this->formatDateLong(Arr::get($suratData, 'tanggal_kegiatan')),
            'tanggal_sidang_panjang'  => $this->formatDateLong(Arr::get($suratData, 'tanggal_sidang')),
            'kelas_info'              => filled(Arr::get($suratData, 'kelas')) ? 'kelas ' . Arr::get($suratData, 'kelas') : '',
            'dosen_pengampu_info'     => filled(Arr::get($suratData, 'dosen_pengampu')) ? ' dengan dosen pengampu ' . Arr::get($suratData, 'dosen_pengampu') : '',
            'ruang_sidang_info'       => filled(Arr::get($suratData, 'ruang_sidang')) ? ' di ruang ' . Arr::get($suratData, 'ruang_sidang') : '',
            default                   => null,
        };
    }

    /**
     * @param  array<string, mixed>  $data
     * @param  array<string, mixed>  $context
     */
    protected function resolveComputedPreviewValue(array $data, array $context, string $sourceKey): mixed
    {
        return match ($sourceKey) {
            'semester_terbilang'      => $this->numberToWords(Arr::get($data, 'semester')),
            'tanggal_surat_panjang'   => $this->formatDateLong(Arr::get($context, 'tanggal_surat', now())),
            'tanggal_yudisium_panjang'=> $this->formatDateLong(Arr::get($data, 'tanggal_yudisium')),
            'tanggal_mulai_panjang'   => $this->formatDateLong(Arr::get($data, 'tanggal_mulai')),
            'tanggal_selesai_panjang' => $this->formatDateLong(Arr::get($data, 'tanggal_selesai')),
            'tanggal_kegiatan_panjang'=> $this->formatDateLong(Arr::get($data, 'tanggal_kegiatan')),
            'tanggal_sidang_panjang'  => $this->formatDateLong(Arr::get($data, 'tanggal_sidang')),
            'kelas_info'              => filled(Arr::get($data, 'kelas')) ? 'kelas ' . Arr::get($data, 'kelas') : '',
            'dosen_pengampu_info'     => filled(Arr::get($data, 'dosen_pengampu')) ? ' dengan dosen pengampu ' . Arr::get($data, 'dosen_pengampu') : '',
            'ruang_sidang_info'       => filled(Arr::get($data, 'ruang_sidang')) ? ' di ruang ' . Arr::get($data, 'ruang_sidang') : '',
            default                   => null,
        };
    }

    protected function resolveSystemValue(string $sourceKey, mixed $fallback = null): mixed
    {
        return match ($sourceKey) {
            'kop_logo_data_uri'     => $this->resolveLogoDataUri($fallback),
            'kota_surat'            => \DB::table('template_global_settings')->where('key', 'kota_surat')->value('value') ?? $fallback ?? 'Cilacap',
            'tanggal_surat_panjang' => now()->locale('id')->isoFormat('D MMMM YYYY'),
            'tanggal_surat'         => now()->locale('id')->isoFormat('D MMMM YYYY'),
            default                 => \DB::table('template_global_settings')->where('key', $sourceKey)->value('value') ?? $fallback,
        };
    }

    protected function resolveLogoDataUri(mixed $fallback = null): string
    {
        $path = public_path('images/kop-logo-temp.png');

        if (!File::exists($path)) {
            return (string) ($fallback ?? '');
        }

        $mimeType = File::mimeType($path) ?: 'image/png';
        $encoded  = base64_encode((string) File::get($path));

        return "data:{$mimeType};base64,{$encoded}";
    }

    // ── Normalize placeholder value ────────────────────────────────────────

    protected function normalizePlaceholderValue(string $key, mixed $value, mixed $fallback = ''): string
    {
        $candidate = $value;

        if (blank($candidate)) {
            $candidate = $fallback;
        }

        if (is_array($candidate)) {
            return $this->formatArrayPlaceholder($key, $candidate);
        }

        if ($candidate instanceof Collection) {
            return $this->formatArrayPlaceholder($key, $candidate->all());
        }

        return (string) ($candidate ?? '');
    }

    // ── Replace placeholders ───────────────────────────────────────────────

    /**
     * @param  array<string, mixed>  $placeholderValues
     */
    protected function replacePlaceholders(string $content, array $placeholderValues): string
    {
        foreach ($placeholderValues as $key => $value) {
            // Skip internal keys (QR data)
            if (str_starts_with((string) $key, '__')) continue;

            $content = str_replace(
                '{{' . $key . '}}',
                nl2br(e((string) $value), false),
                $content
            );
        }

        return preg_replace('/{{\s*[\w\.]+\s*}}/', '', $content) ?? $content;
    }

    // ── Format array placeholder ───────────────────────────────────────────

    /**
     * @param  array<int, mixed>  $items
     */
    protected function formatArrayPlaceholder(string $key, array $items): string
    {
        $normalizedItems = collect($items)
            ->map(static fn ($item): string => trim((string) $item))
            ->filter(static fn ($item): bool => $item !== '')
            ->values();

        if ($normalizedItems->isEmpty()) {
            return '';
        }

        $isYthList = Str::contains(Str::lower($key), 'yth');

        if ($isYthList && $normalizedItems->count() === 1) {
            return 'Yth. ' . $normalizedItems->first();
        }

        if ($isYthList) {
            return "Yth.\n" . $normalizedItems
                ->values()
                ->map(fn (string $item, int $index): string => ($index + 1) . '. ' . $item)
                ->implode("\n");
        }

        if ($normalizedItems->count() === 1) {
            return $normalizedItems->first();
        }

        return $normalizedItems
            ->values()
            ->map(fn (string $item, int $index): string => ($index + 1) . '. ' . $item)
            ->implode("\n");
    }

    // ── Date & number helpers ──────────────────────────────────────────────

    protected function formatDateLong(mixed $value): string
    {
        if ($value instanceof CarbonInterface) {
            return $value->locale('id')->translatedFormat('d F Y');
        }

        if (blank($value)) {
            return '';
        }

        return now()->parse((string) $value)->locale('id')->translatedFormat('d F Y');
    }

    protected function numberToWords(mixed $value): string
    {
        if (!is_numeric($value)) {
            return '';
        }

        $number = (int) $value;

        $words = [
            0  => 'Nol',    1  => 'Satu',    2  => 'Dua',
            3  => 'Tiga',   4  => 'Empat',   5  => 'Lima',
            6  => 'Enam',   7  => 'Tujuh',   8  => 'Delapan',
            9  => 'Sembilan',10 => 'Sepuluh', 11 => 'Sebelas',
            12 => 'Dua Belas', 13 => 'Tiga Belas', 14 => 'Empat Belas',
        ];

        return $words[$number] ?? Str::headline((string) $number);
    }
}