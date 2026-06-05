<?php
// app/Http/Controllers/Admin/ArchiveController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Surat;
use App\Models\JenisSurat;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ArchiveController extends Controller
{
    public function index(Request $request): Response
    {
        $search       = $request->string('search')->trim()->toString();
        $jenisSuratId = $request->integer('jenis_surat_id');
        $dateFrom     = $request->string('date_from')->toString();
        $dateTo       = $request->string('date_to')->toString();

        $query = Surat::query()
            ->with(['pemohon:id,name,nim_nip', 'jenisSurat:id,nama'])
            ->where('status', 'finished')
            ->whereNotNull('generated_file_path')
            ->latest('tanggal_selesai');

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('nomor_surat', 'like', "%{$search}%")
                  ->orWhereHas('pemohon', fn ($u) => $u->where('name', 'like', "%{$search}%"));
            });
        }

        if ($jenisSuratId > 0) {
            $query->where('jenis_surat_id', $jenisSuratId);
        }

        if ($dateFrom !== '') {
            $query->whereDate('tanggal_selesai', '>=', $dateFrom);
        }

        if ($dateTo !== '') {
            $query->whereDate('tanggal_selesai', '<=', $dateTo);
        }

        $surats = $query->paginate(15)
            ->through(fn (Surat $s) => [
                'id'                  => $s->id,
                'nomor_surat'         => $s->nomor_surat,
                'keperluan'           => $s->keperluan,
                'tanggal_selesai'     => $s->tanggal_selesai?->toISOString(),
                'generated_file_path' => $s->generated_file_path,
                'pemohon'             => ['name' => $s->pemohon?->name, 'nim' => $s->pemohon?->nim_nip],
                'jenisSurat'          => ['nama' => $s->jenisSurat?->nama],
                'download_url'        => $s->generated_file_path
                    ? route('admin.surat.generate', $s->id)
                    : null,
            ])
            ->withQueryString();

        return Inertia::render('admin/archive/Index', [
            'surats'      => $surats,
            'filters'     => compact('search', 'date_from', 'date_to') + ['jenis_surat_id' => $jenisSuratId > 0 ? (string) $jenisSuratId : ''],
            'jenisSurats' => JenisSurat::orderBy('nama')->get(['id', 'nama']),
        ]);
    }
}
