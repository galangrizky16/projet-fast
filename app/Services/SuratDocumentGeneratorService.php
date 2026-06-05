<?php

namespace App\Services;

use App\Models\Surat;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use Mpdf\Mpdf;
// use Barryvdh\DomPDF\Facade\Pdf;
// use Throwable;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class SuratDocumentGeneratorService
{
    public function __construct(
        protected SuratTemplateRendererService $renderer,
    ) {
    }

    public function generate(Surat $surat): Surat
    {
        if (blank($surat->nomor_surat)) {
            $surat->forceFill([
                'nomor_surat' => $this->generateNomorSurat($surat),
            ])->save();
        }

        if (blank($surat->qr_token)) {
            $surat->forceFill([
                'qr_token' => (string) Str::uuid(),
            ])->save();
        }

        $rendered     = $this->renderer->renderForSurat($surat->fresh());
        $outputPath   = $this->makeOutputPath($surat);
        $freshSurat   = $surat->fresh(['jenisSurat']);
        $qrCodeBase64 = $this->makeQrCodeBase64($freshSurat);

        $renderedHtml   = is_array($rendered) ? ($rendered['full']   ?? $rendered['html'] ?? '') : (string) $rendered;
        $renderedBody   = is_array($rendered) ? ($rendered['body']   ?? $renderedHtml) : $renderedHtml;
        $renderedHeader = is_array($rendered) ? ($rendered['header'] ?? '') : '';
        $renderedFooter = is_array($rendered) ? ($rendered['footer'] ?? '') : '';

        $viewPayload = [
            'title'        => 'Surat '.$freshSurat->jenisSurat?->nama,
            'html'         => $renderedHtml,
            'bodyHtml'     => $renderedBody,
            'headerHtml'   => $renderedHeader,
            'footerHtml'   => $renderedFooter,
            'styles'       => $this->renderer->documentStyles(),
            'qrCodeBase64' => $qrCodeBase64,
            'qrToken'      => $freshSurat->qr_token,
            'template'     => $freshSurat->jenisSurat?->template,
            'customCss'    => $freshSurat->jenisSurat?->template?->css_style ?? '',
        ];
        // $rendered = $this->renderer->renderForSurat($surat->fresh());
        // $outputPath = $this->makeOutputPath($surat);
        // $freshSurat = $surat->fresh(['jenisSurat']);
        // $qrCodeBase64 = $this->makeQrCodeBase64($freshSurat);
        // $viewPayload = [
        //     'title'        => 'Surat '.$freshSurat->jenisSurat?->nama,
        //     'html'         => $rendered['full'],
        //     'bodyHtml'     => $rendered['body'],
        //     'headerHtml'   => $rendered['header'],
        //     'footerHtml'   => $rendered['footer'],
        //     'styles'       => $this->renderer->documentStyles(),
        //     'qrCodeBase64' => $qrCodeBase64,
        //     'qrToken'      => $freshSurat->qr_token,
        //     'template'     => $freshSurat->jenisSurat?->template,
        //     'customCss'    => $freshSurat->jenisSurat?->template?->css_style ?? '',
        // ];
        Storage::disk('public')->put(
            $outputPath,
            $this->renderPdfOutput($viewPayload),
        );

        $freshSurat->forceFill([
            'rendered_snapshot' => $rendered['html'],
            'generated_file_path' => $outputPath,
            'generated_at' => now(),
            'generated_file_type' => 'pdf',
            'template_version' => $rendered['template']->version,
            'status' => Surat::STATUS_FINISHED,
            'tanggal_selesai' => now(),
            'generated_by'        => auth()->id(),
        ])->save();

        return $freshSurat->fresh();
    }

    protected function generateNomorSurat(Surat $surat): string
    {
        $surat->loadMissing('jenisSurat');

        $code = strtoupper((string) ($surat->jenisSurat?->kode_surat ?: 'SURAT'));
        $year = now()->format('Y');

        return sprintf('B/%04d/%s/FMIKOM/%s', $surat->id, $code, $year);
    }

    protected function makeOutputPath(Surat $surat): string
    {
        $surat->loadMissing('jenisSurat');

        $slug = Str::slug((string) ($surat->jenisSurat?->slug ?: $surat->jenisSurat?->nama ?: 'surat'));

        return sprintf('fast/generated/surat-%d-%s.pdf', $surat->id, $slug);
    }

    protected function makeQrCodeBase64(Surat $surat): string
    {
        // Ganti payload QR menjadi URL endpoint verifikasi
        $baseUrl = config('app.url') ?? 'http://localhost';
        $url = rtrim($baseUrl, '/') . '/verifikasi-qr/' . $surat->qr_token;

        $writer = new Writer(
            new ImageRenderer(
                new RendererStyle(180),
                new SvgImageBackEnd(),
            ),
        );

        return 'data:image/svg+xml;base64,' . base64_encode($writer->writeString($url));
    }

    protected function renderPdfOutput(array $viewPayload): string
    {
        $styles     = (string) ($viewPayload['styles'] ?? '');
        $customCss  = (string) ($viewPayload['customCss'] ?? '');
        $bodyHtml   = (string) ($viewPayload['bodyHtml'] ?? '');
        $headerHtml = (string) ($viewPayload['headerHtml'] ?? '');
        $footerHtml = (string) ($viewPayload['footerHtml'] ?? '');

        // Margin (mm). margin_bottom harus cukup menampung tinggi footer
        // berjalan (kop instansi + alamat + email) agar isi surat di SEMUA
        // halaman tidak menabrak footer.
        $mpdf = new Mpdf([
            'mode'          => 'utf-8',
            'format'        => 'A4',
            'margin_top'    => 48,
            'margin_bottom' => 32,
            'margin_left'   => 15,
            'margin_right'  => 15,
            'margin_header' => 5,
            'margin_footer' => 6,
        ]);

        $mpdf->SetHTMLHeader('<div style="width:100%;">' . $headerHtml . '</div>');
        $mpdf->SetHTMLFooter('<div style="width:100%;">' . $footerHtml . '</div>');

        // Tulis style sekali, lalu body sekali (jangan duplikat).
        $mpdf->WriteHTML("<style>{$styles} {$customCss}</style>", \Mpdf\HTMLParserMode::HEADER_CSS);
        $mpdf->WriteHTML($bodyHtml, \Mpdf\HTMLParserMode::HTML_BODY);

        // QR Code
        if (filled($viewPayload['qrCodeBase64'] ?? null)) {
            $qrCodeBase64 = e((string) $viewPayload['qrCodeBase64']);
            $mpdf->WriteHTML(<<<HTML
            <div style="position: fixed; bottom: 0; right: 0; text-align: right; width: 18mm;">
                <p style="color: #475569; font-size: 6pt; letter-spacing: 0.04em; margin-bottom: 1px; text-transform: uppercase;">Validasi Dokumen</p>
                <img src="{$qrCodeBase64}" style="height: 15mm; width: 15mm;">
            </div>
            HTML);
        }

        return $mpdf->Output('', 'S');
    }

    protected function buildStyles(array $viewPayload): string
    {
        $styles    = (string) ($viewPayload['styles'] ?? '');
        $customCss = (string) ($viewPayload['customCss'] ?? '');
        return "<style>{$styles} {$customCss}</style>";
    }

    // /**
    //  * @param  array<string, mixed>  $viewPayload
    //  */
    // protected function renderPdfOutput(array $viewPayload): string
    // {
    //     try {
    //         return Pdf::loadView('documents.surat', $viewPayload)
    //             ->setPaper('a4', 'portrait')
    //             ->output();
    //     } catch (Throwable) {
    //         return Pdf::loadHTML($this->buildFallbackHtml($viewPayload))
    //             ->setPaper('a4', 'portrait')
    //             ->output();
    //     }
    // }

    // protected function renderPdfOutput(array $viewPayload): string
    // {
    //     return Pdf::loadHTML($this->buildFullHtml($viewPayload))
    //         ->setPaper('a4', 'portrait')
    //         ->output();
    // }

    // protected function buildFullHtml(array $viewPayload): string
    // {
    //     $title      = e((string) ($viewPayload['title'] ?? 'Surat'));
    //     $styles     = (string) ($viewPayload['styles'] ?? '');
    //     $customCss  = (string) ($viewPayload['customCss'] ?? '');
    //     $bodyHtml   = (string) ($viewPayload['bodyHtml'] ?? $viewPayload['html'] ?? '');
    //     $footerHtml = (string) ($viewPayload['footerHtml'] ?? '');
    //     $qrMarkup   = '';

    //     if (filled($viewPayload['qrCodeBase64'] ?? null)) {
    //         $qrCodeBase64 = e((string) $viewPayload['qrCodeBase64']);
    //         $qrMarkup = <<<HTML
    //         <div style="position: fixed; bottom: -22mm; right: 0; text-align: right; width: 18mm;">
    //             <p style="color: #475569; font-size: 6pt; letter-spacing: 0.04em; margin-bottom: 1px; text-transform: uppercase;">Validasi Dokumen</p>
    //             <img src="{$qrCodeBase64}" alt="QR Code Surat" style="height: 15mm; width: 15mm;">
    //         </div>
    //         HTML;
    //         }

    //     return <<<HTML
    //     <!DOCTYPE html>
    //     <html lang="id">
    //     <head>
    //         <meta charset="utf-8">
    //         <title>{$title}</title>
    //         <style>
    //             @page { margin: 48mm 15mm 28mm 15mm; size: A4 portrait; }
    //             body { font-family: "Cambria", "Times New Roman", serif; font-size: 12pt; margin: 0; padding: 0; }
    //             {$styles}
    //             {$customCss}
    //             #kop    { position: fixed; top: -48mm; left: 0; right: 0; }
    //             #footer { position: fixed; bottom: -28mm; left: 0; right: 0; }
    //         </style>
    //     </head>
    //     <body>
    //         <div id="kop">{$headerHtml}</div>
    //         <div id="footer">{$footerHtml}</div>
    //         <div class="page">{$bodyHtml}</div>
    //         {$qrMarkup}
    //     </body>
    //     </html>
    //     HTML;
    // }
//     /**
//      * @param  array<string, mixed>  $viewPayload
//      */
//     protected function buildFallbackHtml(array $viewPayload): string
//     {
//         $qrMarkup = '';

//         if (filled($viewPayload['qrCodeBase64'] ?? null)) {
//             $qrCodeBase64 = e((string) $viewPayload['qrCodeBase64']);

//             $qrMarkup = <<<HTML
// <div style="bottom: 0; position: fixed; right: 0; text-align: right; width: 18mm;">
//     <p style="color: #475569; font-size: 6pt; letter-spacing: 0.04em; margin-bottom: 1px; text-transform: uppercase;">Validasi Dokumen</p>
//     <img src="{$qrCodeBase64}" alt="QR Code Surat" style="height: 15mm; width: 15mm;">
// </div>
// HTML;
//         }

//         return $this->renderer->wrapDocumentHtml(
//             (string) ($viewPayload['title'] ?? 'Surat'),
//             (string) ($viewPayload['html'] ?? '').$qrMarkup,
//             $viewPayload['template'] ?? null,
//         );
//     }
}
