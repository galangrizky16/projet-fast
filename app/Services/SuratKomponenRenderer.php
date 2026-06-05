<?php
// app/Services/SuratKomponenRenderer.php

namespace App\Services;

use App\Models\TemplateGlobalSetting;

class SuratKomponenRenderer
{
    const WARNA_DEFAULT = '#00b050';

    // ── Public API ─────────────────────────────────────────────────────────

    public static function render(array $komponen, array $data = []): string
    {
        $html = '';
        foreach ($komponen as $komp) {
            $html .= static::renderKomponen($komp, $data);
        }
        return $html;
    }

    public static function renderBody(string $body, array $data = []): string
    {
        if (static::isKomponenJson($body)) {
            $komponen = json_decode($body, true) ?? [];
            return static::render($komponen, $data);
        }
        return static::fill($body, $data);
    }

    public static function isKomponenJson(string $body): bool
    {
        $trimmed = trim($body);
        if (!str_starts_with($trimmed, '[')) return false;
        try {
            $decoded = json_decode($trimmed, true, 512, JSON_THROW_ON_ERROR);
            return is_array($decoded) && isset($decoded[0]['type']);
        } catch (\Throwable) {
            return false;
        }
    }

    // ── KOP SURAT ──────────────────────────────────────────────────────────
    // Hanya tampilkan: nama_instansi (dari field nama_instansi)
    // + nama_fakultas + singkatan jika ada
    // Struktur: Logo | Nama Instansi | Nama Fakultas | (Singkatan) | Keputusan | Garis
    public static function renderKop(array $settings = [], array $data = []): string
    {
        $kopHtml = $settings['kop_html'] ?? '';
        if (!empty(trim($kopHtml))) {
            return static::fill($kopHtml, $data);
        }

        $warna        = $settings['warna_primer'] ?? self::WARNA_DEFAULT;
        $namaInstansi = strtoupper(trim($settings['nama_instansi'] ?? 'UNUGHA CILACAP'));
        $namaFakultas = strtoupper(trim($settings['nama_fakultas'] ?? 'FAKULTAS MATEMATIKA DAN ILMU KOMPUTER'));
        $singkatan    = strtoupper(trim($settings['singkatan'] ?? ''));
        $keputusan    = trim($settings['keputusan'] ?? 'Keputusan Kemendikbud RI Nomor : 264/E/O/2014 Tanggal 23 Juli 2014');
        $logoPath     = $settings['logo_path'] ?? '';
        $fontSizeH1   = $settings['font_size_kop_instansi'] ?? '16pt';
        $fontSizeFak  = $settings['font_size_kop_fakultas'] ?? '13pt';

        // Logo
        $logoHtml = '';
        if (!empty($logoPath)) {
            $fullPath = storage_path('app/public/' . $logoPath);
            if (file_exists($fullPath)) {
                $mime    = mime_content_type($fullPath) ?: 'image/png';
                $encoded = base64_encode(file_get_contents($fullPath));
                $logoHtml = "<img src=\"data:{$mime};base64,{$encoded}\" alt=\"Logo\" style=\"display:block; height:70px; margin:0 auto 4px;\">";
            }
        }
        // Fallback logo sementara
        if (empty($logoHtml) && file_exists(public_path('images/kop-logo-temp.png'))) {
            $encoded  = base64_encode(file_get_contents(public_path('images/kop-logo-temp.png')));
            $logoHtml = "<img src=\"data:image/png;base64,{$encoded}\" alt=\"Logo\" style=\"display:block; height:70px; margin:0 auto 4px;\">";
        }

        // Singkatan: hanya tampil jika ada DAN tidak sudah tercakup di nama_instansi
        // Format: NAMA FAKULTAS\n(SINGKATAN) - hanya jika singkatan diisi
        $fakultasHtml = "<div style=\"font-size: {$fontSizeFak}; text-transform: uppercase;\">{$namaFakultas}</div>";
        if (!empty($singkatan)) {
            $fakultasHtml .= "<div style=\"font-size: {$fontSizeFak};\">({$singkatan})</div>";
        }

        $keputusanHtml = !empty($keputusan)
            ? "<p style=\"color: {$warna}; font-size: 9.5pt; line-height: 1.2; margin: 3px 0 5px; text-align: center;\">{$keputusan}</p>"
            : '';

        return <<<HTML
<section style="margin-bottom: 8px; text-align: center;">
    {$logoHtml}
    <div style="color: {$warna}; font-family: 'Times New Roman', serif; font-weight: 700; line-height: 1.1; text-align: center;">
        <div style="font-size: {$fontSizeH1}; letter-spacing: 0.08em;">{$namaInstansi}</div>
        {$fakultasHtml}
    </div>
    {$keputusanHtml}
    <div style="margin: 0 auto; width: 100%;">
        <div style="border-top: 2px solid {$warna}; margin-bottom: 1.5px;"></div>
        <div style="border-top: 0.8px solid {$warna};"></div>
    </div>
</section>
HTML;
    }

    // ── FOOTER ─────────────────────────────────────────────────────────────
    // Persis seperti gambar: Nama Instansi bold | Baris alamat | Baris email+telp+fax
    public static function renderFooter(array $settings = []): string
    {
        $footerHtml = $settings['footer_html'] ?? '';
        if (!empty(trim($footerHtml))) {
            return $footerHtml;
        }

        $warna        = $settings['warna_primer'] ?? self::WARNA_DEFAULT;
        // Footer pakai nama_instansi_footer jika ada, fallback ke nama_instansi
        $namaInstansi = strtoupper(trim(
            $settings['nama_instansi_footer'] ?? $settings['nama_instansi'] ?? 'UNIVERSITAS NAHDLATUL ULAMA AL GHAZALI CILACAP'
        ));
        $alamat  = trim($settings['alamat_footer'] ?? $settings['alamat'] ?? 'Jln. Kemerdekaan Barat No.17 Kesugihan Cilacap Jawa Tengah K.Pos 53274');
        $website = trim($settings['website'] ?? 'http:// unugha.ac.id');
        $email   = trim($settings['email'] ?? 'fmikom@unugha.ac.id');
        $telepon = trim($settings['telepon'] ?? '(0282) 695415, 695407');
        $fax     = trim($settings['fax'] ?? '(0282) 695407');

        // Baris 1: Alamat, website
        $baris1 = trim($alamat . ($website ? ', ' . $website : ''));
        // Baris 2: Email, Telp, Fax
        $baris2Parts = array_filter([
            $email   ? "Email : {$email}" : '',
            $telepon ? "Telp. : {$telepon}" : '',
            $fax     ? "Fax : {$fax}" : '',
        ]);
        $baris2 = implode(' ', $baris2Parts);

        return <<<HTML
        <!-- <div style="position: fixed; bottom: 3mm; left: 0; right: 0; width: 100%;"> -->
            <div style="width: 100%;">
            <div style="border-top: 2px solid {$warna}; margin-bottom: 1.5px;"></div>
            <div style="border-top: 0.8px solid {$warna}; margin-bottom: 3px;"></div>
            <p style="color: {$warna}; font-size: 9.5pt; font-weight: 700; text-align: center; margin: 0; line-height: 1.3; letter-spacing: 0.04em; font-family: 'Times New Roman', serif;">
                {$namaInstansi}
            </p>
            <p style="font-size: 7.8pt; text-align: center; margin: 0; line-height: 1.3; color: {$warna};">
                {$baris1}
            </p>
            <p style="font-size: 7.8pt; text-align: center; margin: 0; line-height: 1.3; color: {$warna};">
                {$baris2}
            </p>
        </div>
        HTML;

        // return <<<HTML
        // <div style="margin-top: 20mm; border-top: 2px solid {$warna}; margin-bottom: 1.5px;"></div>
        // <div style="border-top: 0.8px solid {$warna}; margin-bottom: 3px;"></div>
        // <p style="color: {$warna}; font-size: 9.5pt; font-weight: 700; text-align: center; margin: 0; line-height: 1.3; letter-spacing: 0.04em; font-family: 'Times New Roman', serif;">
        //     {$namaInstansi}
        // </p>
        // <p style="font-size: 7.8pt; text-align: center; margin: 0; line-height: 1.3; color: {$warna};">
        //     {$baris1}
        // </p>
        // <p style="font-size: 7.8pt; text-align: center; margin: 0; line-height: 1.3; color: {$warna};">
        //     {$baris2}
        // </p>
        // HTML;
    }

    // ── Komponen router ────────────────────────────────────────────────────

    protected static function renderKomponen(array $komp, array $data): string
    {
        $html = match ($komp['type'] ?? '') {
            'judul'           => static::renderJudul($komp, $data),
            'subjudul'        => static::renderSubjudul($komp, $data),
            'paragraf'        => static::renderParagraf($komp, $data),
            'paragraf_indent' => static::renderParagrafIndent($komp, $data),
            'header_surat'    => static::renderHeaderSurat($komp, $data),
            'kepada_yth'      => static::renderKepadaYth($komp, $data),
            'tabel_data'      => static::renderTabelData($komp, $data),
            'tabel_indent'    => static::renderTabelIndent($komp, $data),
            'tanda_tangan'    => static::renderTandaTangan($komp, $data),
            'tembusan'        => static::renderTembusan($komp, $data),
            'spasi'           => static::renderSpasi($komp),
            'garis'           => static::renderGaris(),
            default           => '',
        };

        // Wrap dengan margin_left jika ada
        $marginLeft = (int) ($komp['margin_left'] ?? 0);
        if ($marginLeft > 0 && $html !== '') {
            $html = "<div style='margin-left: {$marginLeft}px;'>{$html}</div>\n";
        }

        return $html;
    }

    // ── JUDUL ──────────────────────────────────────────────────────────────
    protected static function renderJudul(array $komp, array $data): string
{
    $teks      = static::fill($komp['teks'] ?? '', $data);
    $align     = $komp['align'] ?? 'center';
    $size      = $komp['font_size'] ?? '12pt';
    $bold      = ($komp['bold'] ?? true) ? 'font-weight: bold;' : 'font-weight: normal;';
    $underline = ($komp['underline'] ?? false) ? 'text-decoration: underline; text-decoration-thickness: 2px;' : '';
    
    return "<h2 style=\"margin: 0 0 2px 0; text-align: {$align}; font-size: {$size}; {$bold} {$underline} text-transform: uppercase;\">{$teks}</h2>\n";
}

    // ── SUBJUDUL ───────────────────────────────────────────────────────────
    protected static function renderSubjudul(array $komp, array $data): string
    {
        $teks = static::fill($komp['teks'] ?? '', $data);
        return "<p style=\"margin: 0 0 10px 0; text-align: center; font-size: 11pt;\">{$teks}</p>\n";
    }

    // ── PARAGRAF ───────────────────────────────────────────────────────────
    // Mendukung text_indent untuk baris pertama menjorok
    protected static function renderParagraf(array $komp, array $data): string
    {
        $teks        = static::fill($komp['teks'] ?? '', $data);
        $align       = $komp['align'] ?? 'justify';
        $italic      = ($komp['italic'] ?? false) ? 'font-style: italic;' : '';
        $bold        = ($komp['bold'] ?? false) ? 'font-weight: bold;' : '';
        $textIndent  = isset($komp['text_indent']) && $komp['text_indent'] > 0
                        ? "text-indent: {$komp['text_indent']}px;" : '';
        $size        = $komp['font_size'] ?? '11pt';
        $teks        = nl2br($teks);
        return "<p style=\"margin: 0 0 8px 0; text-align: {$align}; font-size: {$size}; line-height: 1.5; {$italic} {$bold} {$textIndent}\">{$teks}</p>\n";
    }

    // ── PARAGRAF INDENT (seluruh blok menjorok) ────────────────────────────
    protected static function renderParagrafIndent(array $komp, array $data): string
    {
        $teks        = static::fill($komp['teks'] ?? '', $data);
        $align       = $komp['align'] ?? 'justify';
        $indent      = $komp['indent'] ?? 40;
        $italic      = ($komp['italic'] ?? false) ? 'font-style: italic;' : '';
        $bold        = ($komp['bold'] ?? false) ? 'font-weight: bold;' : '';
        $textIndent  = isset($komp['text_indent']) && $komp['text_indent'] > 0
                        ? "text-indent: {$komp['text_indent']}px;" : '';
        $size        = $komp['font_size'] ?? '11pt';
        $teks        = nl2br($teks);
        return "<p style=\"margin: 0 0 8px {$indent}px; text-align: {$align}; font-size: {$size}; line-height: 1.5; {$italic} {$bold} {$textIndent}\">{$teks}</p>\n";
    }

    // ── HEADER SURAT (Nomor/Lampiran/Perihal kiri | Kota,Tanggal kanan) ───
    protected static function renderHeaderSurat(array $komp, array $data): string
    {
        $nomor    = static::fill($komp['nomor'] ?? '{{nomor_surat}}', $data);
        $lampiran = static::fill($komp['lampiran'] ?? '-', $data);
        $perihal  = static::fill($komp['perihal'] ?? '', $data);
        $kota     = static::fill($komp['kota'] ?? '{{kota_surat}}', $data);
        $tanggal  = static::fill($komp['tanggal'] ?? '{{tanggal_surat_panjang}}', $data);
        $size     = $komp['font_size'] ?? '11pt';

        return <<<HTML
<table style="width: 100%; border-collapse: collapse; margin-bottom: 14px; font-size: {$size}; line-height: 1.7;">
    <tbody>
        <tr>
            <td style="width: 55%; vertical-align: top; padding: 0;">
                <table style="border-collapse: collapse; font-size: {$size};">
                    <tr>
                        <td style="width: 80px; padding: 0; vertical-align: top;">Nomor</td>
                        <td style="width: 16px; padding: 0 4px; vertical-align: top; text-align: center;">:</td>
                        <td style="padding: 0;">{$nomor}</td>
                    </tr>
                    <tr>
                        <td style="padding: 0; vertical-align: top;">Lampiran</td>
                        <td style="padding: 0; text-align: center;">:</td>
                        <td style="padding: 0;">{$lampiran}</td>
                    </tr>
                    <tr>
                        <td style="padding: 0; vertical-align: top;">Perihal</td>
                        <td style="padding: 0; text-align: center;">:</td>
                        <td style="padding: 0;">{$perihal}</td>
                    </tr>
                </table>
            </td>
            <td style="width: 45%; vertical-align: top; text-align: right; padding: 0; font-size: {$size};">
                {$kota}, {$tanggal}
            </td>
        </tr>
    </tbody>
</table>
HTML;
    }

    // ── KEPADA YTH ─────────────────────────────────────────────────────────
    protected static function renderKepadaYth(array $komp, array $data): string
    {
        $penerima = $komp['penerima'] ?? [];
        $lokasi   = static::fill($komp['lokasi'] ?? 'di-', $data);
        $tempat   = static::fill($komp['tempat'] ?? 'Tempat', $data);
        $size     = $komp['font_size'] ?? '11pt';

        if (count($penerima) === 1) {
            $penerimaHtml = "<p style=\"margin: 0; font-size: {$size};\">Yth. " . static::fill($penerima[0], $data) . "</p>\n";
        } else {
            $penerimaHtml = "<p style=\"margin: 0 0 2px 0; font-size: {$size};\">Yth.</p>\n";
            foreach ($penerima as $i => $p) {
                $no = $i + 1;
                $penerimaHtml .= "<p style=\"margin: 0 0 1px 0; font-size: {$size}; font-weight: bold;\">{$no}. " . static::fill($p, $data) . "</p>\n";
            }
        }

        return <<<HTML
<div style="margin-bottom: 12px; font-size: {$size}; line-height: 1.6;">
    {$penerimaHtml}
    <p style="margin: 0; font-size: {$size};">{$lokasi}</p>
    <p style="margin: 0 0 0 20px; font-size: {$size};">{$tempat}</p>
</div>
HTML;
    }

    // ── TABEL DATA ─────────────────────────────────────────────────────────
    protected static function renderTabelData(array $komp, array $data): string
    {
        $rows = $komp['rows'] ?? [];
        if (empty($rows)) return '';
        $size = $komp['font_size'] ?? '11pt';

        $html = "<table style=\"width: 100%; border-collapse: collapse; margin-bottom: 8px; font-size: {$size};\">\n<tbody>\n";
        foreach ($rows as $row) {
            $label = htmlspecialchars($row['label'] ?? '');
            $nilai = static::fill($row['nilai'] ?? '', $data);
            $html .= "<tr>
                <td style=\"width: 130px; padding: 1.5px 0; vertical-align: top;\">{$label}</td>
                <td style=\"width: 12px; padding: 1.5px 0; vertical-align: top; text-align: center;\">:</td>
                <td style=\"padding: 1.5px 0; vertical-align: top;\">{$nilai}</td>
            </tr>\n";
        }
        $html .= "</tbody>\n</table>\n";
        return $html;
    }

    // ── TABEL INDENT ───────────────────────────────────────────────────────
    protected static function renderTabelIndent(array $komp, array $data): string
    {
        $rows   = $komp['rows'] ?? [];
        $indent = $komp['indent'] ?? 40;
        if (empty($rows)) return '';
        $size = $komp['font_size'] ?? '11pt';

        $html = "<table style=\"margin-left: {$indent}px; border-collapse: collapse; margin-bottom: 8px; font-size: {$size};\">\n<tbody>\n";
        foreach ($rows as $row) {
            $label = htmlspecialchars($row['label'] ?? '');
            $nilai = static::fill($row['nilai'] ?? '', $data);
            $html .= "<tr>
                <td style=\"width: 110px; padding: 1.5px 0; vertical-align: top;\">{$label}</td>
                <td style=\"width: 12px; padding: 1.5px 0; vertical-align: top; text-align: center;\">:</td>
                <td style=\"padding: 1.5px 0; vertical-align: top;\">{$nilai}</td>
            </tr>\n";
        }
        $html .= "</tbody>\n</table>\n";
        return $html;
    }

    // ── TANDA TANGAN + QR ─────────────────────────────────────────────────
    // Layout: kolom TTD (1/2/3 kolom) + QR terpisah di kanan bawah
    // $data['__qr_svg']    = SVG string QR
    // $data['__qr_active'] = bool
    protected static function renderTandaTangan(array $komp, array $data): string
    {
        $kolom  = $komp['kolom'] ?? [];
        if (empty($kolom)) return '';
        $size   = $komp['font_size'] ?? '11pt';
        $jumlah = count($kolom);

        // ── QR Code ────────────────────────────────────────────────────
        $qrSvg    = $data['__qr_svg'] ?? '';
        $qrActive = $data['__qr_active'] ?? false;

        if ($qrActive && !empty($qrSvg)) {
            // 3x3cm = 113px @ 96dpi; untuk PDF renderer biasanya 1cm ~ 37.8px
            $qrHtml = <<<HTML
<div style="display: inline-block; text-align: center;">
    <div style="width: 113px; height: 113px;">
        {$qrSvg}
    </div>
    <p style="margin: 2px 0 0 0; font-size: 7pt; text-align: center; font-weight: bold; letter-spacing: 0.02em;">Dokumen Terverifikasi</p>
</div>
HTML;
        } else {
            $qrHtml = <<<HTML
<div style="display: inline-block; text-align: center;">
    <div style="width: 113px; height: 113px; border: 1.5px dashed #CBD5E1; background: #F8FAFC; display: flex; align-items: center; justify-content: center;">
        <p style="font-size: 7pt; color: #94A3B8; text-align: center; margin: 0; line-height: 1.5; padding: 8px;">QR Code akan muncul setelah disetujui</p>
    </div>
    <p style="margin: 2px 0 0 0; font-size: 7pt; text-align: center; color: #94A3B8;">Menunggu Persetujuan</p>
</div>
HTML;
        }

        // ── Posisi teks per kolom ───────────────────────────────────────
        // 1 kolom → kanan
        // 2 kolom → kiri, kanan
        // 3 kolom → kiri, tengah, kanan
        $alignMap = match ($jumlah) {
            1 => ['right'],
            2 => ['left', 'right'],
            3 => ['left', 'center', 'right'],
            default => array_fill(0, $jumlah, 'center'),
        };

        // ── Render kolom TTD ───────────────────────────────────────────
        $ttdCols = '';
        foreach ($kolom as $i => $kol) {
            $jabatan   = static::fill($kol['jabatan'] ?? '', $data);
            $nama      = static::fill($kol['nama'] ?? '', $data);
            $nik       = static::fill($kol['nik'] ?? '', $data);
            $textAlign = $alignMap[$i] ?? 'center';
            $ttdCols  .= <<<HTML
<td style="vertical-align: top; text-align: {$textAlign}; padding: 0 8px 0 0; font-size: {$size};">
    <p style="margin: 0 0 3px 0;">{$jabatan}</p>
    <div style="height: 50px;"></div>
    <p style="margin: 0; font-weight: bold;">{$nama}</p>
    <p style="margin: 0;">{$nik}</p>
</td>
HTML;
        }

        return <<<HTML
<table style="width: 100%; border-collapse: collapse; margin-top: 16px; font-size: {$size};">
    <tbody>
        <tr>
            {$ttdCols}
            <td style="width: 130px; vertical-align: top; text-align: center; padding: 0 0 0 8px;">
                {$qrHtml}
            </td>
        </tr>
    </tbody>
    <div style="height: 35mm; display: block;"></div>
</table>
HTML;
    }

    // ── TEMBUSAN ───────────────────────────────────────────────────────────
    protected static function renderTembusan(array $komp, array $data): string
    {
        $items = $komp['items'] ?? [];
        if (empty($items)) return '';
        $size = $komp['font_size'] ?? '10.5pt';

        $html = "<div style=\"font-size: {$size}; line-height: 1.4; margin-top: 8px;\">\n";
        $html .= "<p style=\"margin: 0 0 2px 0;\">Tembusan:</p>\n";
        foreach ($items as $i => $item) {
            $no    = $i + 1;
            $label = static::fill($item, $data);
            $html .= "<p style=\"margin: 0;\">{$no}. {$label}</p>\n";
        }
        $html .= "</div>\n";
        return $html;
    }

    // ── SPASI ──────────────────────────────────────────────────────────────
    protected static function renderSpasi(array $komp): string
    {
        $tinggi = intval($komp['tinggi'] ?? 20);
        return "<div style=\"height: {$tinggi}px;\"></div>\n";
    }

    // ── GARIS ──────────────────────────────────────────────────────────────
    protected static function renderGaris(): string
    {
        return "<hr style=\"border: none; border-top: 1px solid #CBD5E1; margin: 8px 0;\" />\n";
    }

    // ── Helper fill placeholder ────────────────────────────────────────────
    protected static function fill(string $teks, array $data): string
    {
        foreach ($data as $key => $value) {
            if (is_array($value)) $value = implode(', ', $value);
            $teks = str_replace(
                ['{{' . $key . '}}', '{{ ' . $key . ' }}'],
                htmlspecialchars((string) $value),
                $teks
            );
        }
        return $teks;
    }
}