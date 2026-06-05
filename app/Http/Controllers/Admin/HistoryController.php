<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Surat;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class HistoryController extends Controller
{
    public function index(Request $request): Response
    {
        $search = $request->string('search')->trim()->toString();
        $status = $request->string('status')->toString();

        $query = Surat::query()
            ->with(['pemohon:id,name,nim_nip', 'jenisSurat:id,nama'])
            ->where('type', 'surat_keluar')
            ->where('pemohon_id', auth()->id())
            ->latest();

        if ($search !== '') {
            $query->where(function ($q) use ($search): void {
                $q->where('nomor_surat', 'like', "%{$search}%")
                  ->orWhere('keperluan', 'like', "%{$search}%")
                  ->orWhereHas('pemohon', fn ($u) => $u->where('name', 'like', "%{$search}%"));
            });
        }

        if ($status !== '') {
            $query->where('status', $status);
        }

        $surats = $query->paginate(20)
            ->through(fn (Surat $surat): array => [
                'id' => $surat->id,
                'nomor_surat' => $surat->nomor_surat,
                'status' => $surat->status,
                'keperluan' => $surat->keperluan,
                'tanggal_pengajuan' => $surat->tanggal_pengajuan?->toISOString(),
                'tanggal_selesai' => $surat->tanggal_selesai?->toISOString(),
                'pemohon' => ['name' => $surat->pemohon?->name],
                'jenisSurat' => ['nama' => $surat->jenisSurat?->nama],
            ])
            ->withQueryString();

        return Inertia::render('admin/history/Index', [
            'surats' => $surats,
            'filters' => compact('search', 'status'),
        ]);
    }
}
