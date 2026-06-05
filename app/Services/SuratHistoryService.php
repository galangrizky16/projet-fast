<?php
// app/Services/SuratHistoryService.php

namespace App\Services;

use App\Models\SuratHistory;
use Illuminate\Support\Facades\Auth;

class SuratHistoryService
{
    /**
     * Catat aksi ke surat_histories.
     */
    public static function record(
        int $suratId,
        string $action,
        string $label,
        array $extra = []
    ): SuratHistory {
        return SuratHistory::create([
            'surat_id'     => $suratId,
            'user_id'      => Auth::id(),
            'action'       => $action,
            'action_label' => $label,
            'keterangan'   => $extra['keterangan'] ?? null,
            'meta'         => $extra['meta'] ?? null,
            'ip_address'   => request()?->ip(),
        ]);
    }

    // ── Shortcut methods ──────────────────────────────────────────

    public static function created(int $suratId, string $jenisSurat): SuratHistory
    {
        return static::record($suratId, SuratHistory::ACTION_CREATED,
            "Surat {$jenisSurat} dibuat");
    }

    public static function submitted(int $suratId, string $jenisSurat): SuratHistory
    {
        return static::record($suratId, SuratHistory::ACTION_SUBMITTED,
            "Surat {$jenisSurat} diajukan");
    }

    public static function validated(int $suratId, ?string $catatan = null): SuratHistory
    {
        return static::record($suratId, SuratHistory::ACTION_VALIDATED,
            'Surat divalidasi admin',
            ['keterangan' => $catatan]);
    }

    public static function approved(int $suratId, string $roleNama, ?string $catatan = null): SuratHistory
    {
        return static::record($suratId, SuratHistory::ACTION_APPROVED,
            "Surat disetujui {$roleNama}",
            ['keterangan' => $catatan]);
    }

    public static function rejected(int $suratId, string $alasan): SuratHistory
    {
        return static::record($suratId, SuratHistory::ACTION_REJECTED,
            'Surat ditolak',
            ['keterangan' => $alasan]);
    }

    public static function generated(int $suratId, string $nomorSurat): SuratHistory
    {
        return static::record($suratId, SuratHistory::ACTION_GENERATED,
            "Dokumen PDF digenerate — No: {$nomorSurat}");
    }

    public static function printed(int $suratId): SuratHistory
    {
        return static::record($suratId, SuratHistory::ACTION_PRINTED,
            'Surat dicetak');
    }

    public static function qrRevoked(int $suratId, string $alasan): SuratHistory
    {
        return static::record($suratId, SuratHistory::ACTION_QR_REVOKED,
            'QR Code dicabut',
            ['keterangan' => $alasan]);
    }

    public static function revised(int $suratId, int $revisiKe, ?string $catatan = null): SuratHistory
    {
        return static::record($suratId, SuratHistory::ACTION_REVISED,
            "Surat direvisi (revisi ke-{$revisiKe})",
            ['keterangan' => $catatan]);
    }
}
