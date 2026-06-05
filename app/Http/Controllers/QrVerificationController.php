<?php

namespace App\Http\Controllers;

use App\Models\Surat;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class QrVerificationController extends Controller
{
    // Tampilkan form input manual token QR
    public function showForm(): Response
    {
        return Inertia::render('qr/VerifyForm');
    }

    // Verifikasi QR berdasarkan token
public function verify(Request $request, string $token): Response
{
    $surat = Surat::where('qr_token', $token)
        ->with(['pemohon', 'jenisSurat.category'])
        ->first();

    if (!$surat) {
        return Inertia::render('qr/VerifyResult', [
            'found'   => false,
            'valid'   => false,
            'revoked' => false,
            'message' => 'Dokumen tidak ditemukan atau tidak valid.',
            'surat'   => null,
        ]);
    }

    $isValidated = $surat->status === 'finished';
    $isRevoked   = $surat->qrCode?->status === 'revoked';

    // Catat pertama kali QR di-scan setelah divalidasi
    if ($isValidated && !$isRevoked && !$surat->qr_validated_at) {
        $surat->update(['qr_validated_at' => now()]);
    }

    return Inertia::render('qr/VerifyResult', [
        'found'   => true,
        'valid'   => $isValidated && !$isRevoked,
        'revoked' => $isRevoked,
        'message' => $isRevoked
            ? 'Dokumen ini telah dicabut dan tidak berlaku.'
            : ($isValidated
                ? 'Dokumen ini sah dan telah divalidasi.'
                : 'Dokumen belum divalidasi.'),
        'surat'   => [
            'nomor_surat'    => $surat->nomor_surat,
            'jenis_surat'    => $surat->jenisSurat?->nama,
            'kategori'       => $surat->jenisSurat?->category?->nama,
            'nama_pemohon'   => $surat->pemohon?->name,
            'nim'            => $surat->pemohon?->nim_nip ?? $surat->pemohon?->nomor_induk,
            'keperluan'      => $surat->keperluan,
            'tanggal_terbit' => $surat->generated_at?->toISOString(),
            'status'         => $isRevoked ? 'dicabut' : ($isValidated ? 'valid' : 'belum_divalidasi'),
        ],
    ]);
}
}