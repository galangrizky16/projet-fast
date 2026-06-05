<?php
// app/Http/Controllers/Admin/QrManageController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SuratQrCode;
use App\Models\Surat;
use App\Services\SuratHistoryService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class QrManageController extends Controller
{
    public function index(Request $request): Response
    {
        $search = $request->string('search')->trim()->toString();
        $status = $request->string('status')->toString();

        // Ambil dari tabel surat_qr_codes jika ada, fallback ke surats.qr_token
        $query = Surat::query()
            ->with(['pemohon:id,name,nim_nip', 'jenisSurat:id,nama'])
            ->whereNotNull('qr_token')
            ->latest();

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('nomor_surat', 'like', "%{$search}%")
                  ->orWhereHas('pemohon', fn ($u) => $u->where('name', 'like', "%{$search}%"));
            });
        }

        if ($status !== '') {
            $query->where('status', $status);
        }

        $surats = $query->paginate(15)
            ->through(fn (Surat $s) => [
                'id'          => $s->id,
                'nomor_surat' => $s->nomor_surat,
                'status'      => $s->status,
                'qr_token'    => $s->qr_token,
                'created_at'  => $s->created_at?->toISOString(),
                'pemohon'     => ['name' => $s->pemohon?->name],
                'jenisSurat'  => ['nama' => $s->jenisSurat?->nama],
                // Cek dari tabel surat_qr_codes
                'qr_status'   => $s->qrCode?->status ?? 'active',
                'qr_revoked_at' => $s->qrCode?->revoked_at?->toISOString(),
            ])
            ->withQueryString();

        return Inertia::render('admin/qr/Index', [
            'surats'  => $surats,
            'filters' => compact('search', 'status'),
        ]);
    }

    public function revoke(Request $request, int $suratId)
    {
        $request->validate([
            'alasan' => 'nullable|string|max:255',
        ]);

        $surat = Surat::findOrFail($suratId);

        // Update di tabel surat_qr_codes jika ada
        $qrCode = SuratQrCode::where('surat_id', $suratId)
            ->where('status', 'active')
            ->first();

        if ($qrCode) {
            $qrCode->revoke(auth()->id(), $request->alasan ?? '');
        }

        // Catat history
        SuratHistoryService::qrRevoked($suratId, $request->alasan ?? 'Dicabut oleh admin');

        return back()->with('success', 'QR Code berhasil dicabut.');
    }
}
