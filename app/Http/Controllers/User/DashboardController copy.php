<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\JenisSurat;
use App\Models\Surat;
use App\Models\SuratCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(Request $request): Response
    {
        Log::info('Dashboard accessed', ['user' => $request->user()?->id]);
        $user = $request->user();
        abort_if($user === null, 403);

        $user->loadMissing('role');
        $roleSlug = str((string) ($user->role?->slug ?? ''))->slug()->toString();
        $isLecturer = str($roleSlug)->contains('dosen');
        $roleId = $user->role?->id;

        $baseQuery = Surat::query()
            ->with([
                'jenisSurat',
                'approvalFlows' => fn($query) => $query->latest('tanggal_aksi')->latest('id'),
            ])
            ->where('pemohon_id', $user->id);

        $jenisSurats = JenisSurat::query()
            ->with('category')
            ->where('is_active', true)
            ->where('alur_pengajuan', 'submission')
            ->when($roleId !== null, function ($query) use ($roleId): void {
                $query->where(function ($roleQuery) use ($roleId): void {
                    $roleQuery
                        ->whereNull('allowed_role_id')
                        ->orWhere('allowed_role_id', $roleId);
                });
            })
            ->orderBy('nama')
            ->get(['id', 'category_id', 'nama', 'slug', 'deskripsi']);

        $categoryIds = $jenisSurats
            ->pluck('category_id')
            ->filter()
            ->unique()
            ->values();

        $categories = SuratCategory::query()
            ->where('is_active', true)
            ->when($categoryIds->isNotEmpty(), fn($query) => $query->whereIn('id', $categoryIds))
            ->orderBy('urutan')
            ->orderBy('nama')
            ->get(['id', 'nama', 'slug', 'deskripsi']);

        return Inertia::render('fast/user/Dashboard', [
            'summary' => [
                'total' => (clone $baseQuery)->count(),
                'pending' => (clone $baseQuery)->where('status', Surat::STATUS_PENDING)->count(),
                'approved' => (clone $baseQuery)->whereIn('status', [
                    Surat::STATUS_VALIDATED_ADMIN,
                    Surat::STATUS_APPROVED_KAPRODI,
                    Surat::STATUS_APPROVED_DEKAN,
                    Surat::STATUS_FINISHED,
                ])->count(),
                'rejected' => (clone $baseQuery)->where('status', Surat::STATUS_REJECTED)->count(),
            ],
            'latest' => (clone $baseQuery)
                ->latest('tanggal_pengajuan')
                ->latest('id')
                ->limit(5)
                ->get()
                ->map(fn(Surat $surat): array => [
                    'id' => $surat->id,
                    'reference' => $surat->nomor_surat ?: sprintf('REQ-%05d', $surat->id),
                    'jenisSurat' => $surat->jenisSurat?->nama ?? 'Surat Akademik',
                    'status' => $surat->status,
                    'keperluan' => $surat->keperluan,
                    'rejectionReason' => $surat->approvalFlows
                        ->firstWhere('status', \App\Models\SuratApprovalFlow::STATUS_REJECTED)?->catatan,
                    'submittedAt' => optional($surat->tanggal_pengajuan ?? $surat->created_at)?->toISOString(),
                    'neededAt' => optional($surat->tanggal_kebutuhan)?->toDateString(),
                ])
                ->values(),
            'categories' => $categories->map(fn(SuratCategory $category): array => [
                'id' => $category->id,
                'nama' => $category->nama,
                'slug' => $category->slug,
                'deskripsi' => $category->deskripsi,
            ])->values(),
            'jenisSurats' => $jenisSurats->map(fn(JenisSurat $jenisSurat): array => [
                'id' => $jenisSurat->id,
                'categoryId' => $jenisSurat->category_id,
                'nama' => $jenisSurat->nama,
                'slug' => $jenisSurat->slug,
                'deskripsi' => $jenisSurat->deskripsi,
            ])->values(),
            'userRole' => [
                'id' => $user->role?->id,
                'name' => $user->role?->nama,
                'slug' => $user->role?->slug,
            ],
            'userProfile' => [
                'name' => $user->name,
                'identifierLabel' => $isLecturer ? 'NIP' : 'NIM',
                'identifierValue' => $user->nim_nip ?: $user->nomor_induk,
            ],
            'endpoints' => [
                'submission' => route('fast.user.submissions.store', absolute: false),
                'jenisSuratBase' => '/jenis-surat',
            ],
        ]);
    }
}
