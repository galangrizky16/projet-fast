<?php
// app/Http/Controllers/Admin/HistoryController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SuratHistory;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class HistoryController extends Controller
{
    public function index(Request $request): Response
    {
        $search   = $request->string('search')->trim()->toString();
        $action   = $request->string('action')->toString();
        $dateFrom = $request->string('date_from')->toString();
        $dateTo   = $request->string('date_to')->toString();

        $query = SuratHistory::query()
            ->with(['user:id,name', 'surat.jenisSurat:id,nama'])
            ->latest();

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('action_label', 'like', "%{$search}%")
                  ->orWhere('keterangan', 'like', "%{$search}%")
                  ->orWhereHas('user', fn ($u) => $u->where('name', 'like', "%{$search}%"))
                  ->orWhereHas('surat.jenisSurat', fn ($j) => $j->where('nama', 'like', "%{$search}%"));
            });
        }

        if ($action !== '') {
            $query->where('action', $action);
        }

        if ($dateFrom !== '') {
            $query->whereDate('created_at', '>=', $dateFrom);
        }

        if ($dateTo !== '') {
            $query->whereDate('created_at', '<=', $dateTo);
        }

        $histories = $query->paginate(20)
            ->through(fn (SuratHistory $h) => [
                'id'           => $h->id,
                'action'       => $h->action,
                'action_label' => $h->action_label,
                'keterangan'   => $h->keterangan,
                'created_at'   => $h->created_at?->toISOString(),
                'user'         => ['name' => $h->user?->name],
                'surat'        => $h->surat ? [
                    'id'          => $h->surat->id,
                    'nomor_surat' => $h->surat->nomor_surat,
                    'jenisSurat'  => ['nama' => $h->surat->jenisSurat?->nama],
                ] : null,
            ])
            ->withQueryString();

        return Inertia::render('admin/history/Index', [
            'histories' => $histories,
            'filters'   => [
                'search'    => $search,
                'action'    => $action,
                'date_from' => $dateFrom,
                'date_to'   => $dateTo,
            ],
        ]);
    }
}
