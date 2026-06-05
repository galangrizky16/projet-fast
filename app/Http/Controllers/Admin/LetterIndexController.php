<?php
// app/Http/Controllers/Admin/LetterIndexController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\JenisSurat;
use App\Models\Surat;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class LetterIndexController extends Controller
{
    public function index(Request $request): Response
    {
        $search       = $request->string('search')->trim()->toString();
        $status       = $request->string('status')->toString();
        $jenisSuratId = $request->integer('jenis_surat_id');

        $query = Surat::query()
            ->with(['pemohon', 'jenisSurat.category'])
            ->latest();

        if ($status !== '') {
            $query->where('status', $status);
        }

        if ($jenisSuratId > 0) {
            $query->where('jenis_surat_id', $jenisSuratId);
        }

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('nomor_surat', 'like', "%{$search}%")
                  ->orWhere('keperluan', 'like', "%{$search}%")
                  ->orWhereHas('pemohon', fn ($u) =>
                      $u->where('name', 'like', "%{$search}%")
                        ->orWhere('nim_nip', 'like', "%{$search}%")
                  );
            });
        }

        $surats = $query->paginate(15)
            ->through(fn (Surat $surat) => [
                'id'               => $surat->id,
                'nomor_surat'      => $surat->nomor_surat,
                'status'           => $surat->status,
                'keperluan'        => $surat->keperluan,
                'tanggal_pengajuan' => $surat->tanggal_pengajuan?->toISOString(),
                'tanggal_selesai'  => $surat->tanggal_selesai?->toISOString(),
                'created_at'       => $surat->created_at?->toISOString(),
                'pemohon' => [
                    'name' => $surat->pemohon?->name,
                    'nim'  => $surat->pemohon?->nim_nip ?? $surat->pemohon?->nomor_induk,
                ],
                'jenisSurat' => [
                    'id'       => $surat->jenisSurat?->id,
                    'nama'     => $surat->jenisSurat?->nama,
                    'category' => ['nama' => $surat->jenisSurat?->category?->nama],
                ],
            ])
            ->withQueryString();

        return Inertia::render('admin/letters/Index', [
            'surats'     => $surats,
            'filters'    => compact('search', 'status') + ['jenis_surat_id' => $jenisSuratId > 0 ? (string) $jenisSuratId : ''],
            'jenisSurats' => JenisSurat::orderBy('nama')->get(['id', 'nama']),
            'summary'    => [
                'total'    => Surat::count(),
                'pending'  => Surat::where('status', Surat::STATUS_PENDING)->count(),
                'finished' => Surat::where('status', Surat::STATUS_FINISHED)->count(),
                'rejected' => Surat::where('status', Surat::STATUS_REJECTED)->count(),
            ],
        ]);
    }
}
