<?php

namespace App\Services;

use App\Models\Surat;
use App\Models\SuratApprovalFlow;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class FastApprovalWorkflowService
{
    public function __construct(
        protected SuratDocumentGeneratorService $documentGenerator,
    ) {
    }

    public const ROLE_ADMIN = 'admin';

    public const ROLE_KAPRODI = 'kaprodi';

    public const ROLE_DEKAN = 'dekan';

    public function approve(Surat $surat, string $role, ?User $actor = null, ?string $notes = null): SuratApprovalFlow
    {
        $this->guardApproval($surat, $role);
        $surat->loadMissing('jenisSurat.approvalRole');

        return DB::transaction(function () use ($surat, $role, $actor, $notes): SuratApprovalFlow {
            $flow = $surat->approvalFlows()->create([
                'approver_id' => $actor?->id,
                'urutan' => $this->resolveOrder($role),
                'role' => $role,
                'status' => SuratApprovalFlow::STATUS_APPROVED,
                'keterangan' => $this->approvalLabel($role),
                'catatan' => $notes,
                'approved_at' => now(),
                'tanggal_aksi' => now(),
            ]);

            $surat->update([
                'status' => $this->nextStatusForSurat($surat, $role),
            ]);

            if ($this->shouldGenerateAfterApproval($surat, $role)) {
                $this->documentGenerator->generate($surat->fresh());
            }

            return $flow;
        });
    }

    public function reject(Surat $surat, string $role, ?User $actor = null, ?string $notes = null): SuratApprovalFlow
    {
        $this->guardRejection($surat, $role);

        return DB::transaction(function () use ($surat, $role, $actor, $notes): SuratApprovalFlow {
            $flow = $surat->approvalFlows()->create([
                'approver_id' => $actor?->id,
                'urutan' => $this->resolveOrder($role),
                'role' => $role,
                'status' => SuratApprovalFlow::STATUS_REJECTED,
                'keterangan' => 'Ditolak '.strtoupper($role),
                'catatan' => $notes,
                'approved_at' => now(),
                'tanggal_aksi' => now(),
            ]);

            $surat->update([
                'status' => Surat::STATUS_REJECTED,
            ]);

            return $flow;
        });
    }

    public function nextStatusForApproval(string $role): string
    {
        return match ($role) {
            self::ROLE_ADMIN => throw new InvalidArgumentException('Status admin harus ditentukan dari approval_role_id jenis surat.'),
            self::ROLE_KAPRODI => Surat::STATUS_APPROVED_KAPRODI,
            self::ROLE_DEKAN => Surat::STATUS_APPROVED_DEKAN,
            default => throw new InvalidArgumentException('Role approval tidak dikenali.'),
        };
    }

    public function resolveOrder(string $role): int
    {
        return match ($role) {
            self::ROLE_ADMIN => 1,
            self::ROLE_KAPRODI => 2,
            self::ROLE_DEKAN => 3,
            default => throw new InvalidArgumentException('Urutan approval tidak dikenali.'),
        };
    }

    protected function approvalLabel(string $role): string
    {
        return match ($role) {
            self::ROLE_ADMIN => 'Validasi Admin',
            self::ROLE_KAPRODI => 'Disetujui Kaprodi',
            self::ROLE_DEKAN => 'Disetujui Dekan',
            default => 'Approval',
        };
    }

    protected function guardApproval(Surat $surat, string $role): void
    {
        if (! $surat->canBeApprovedByRole($role)) {
            abort(403, 'Status surat tidak sesuai untuk approval ini.');
        }
    }

    protected function guardRejection(Surat $surat, string $role): void
    {
        if (! $surat->canBeRejectedByRole($role)) {
            abort(403, 'Status surat tidak sesuai untuk penolakan ini.');
        }
    }

    public function nextStatusForSurat(Surat $surat, string $role): string
    {
        if ($role === self::ROLE_ADMIN) {
            return $surat->requiresFinalApproval()
                ? Surat::STATUS_VALIDATED_ADMIN
                : Surat::STATUS_FINISHED;
        }

        return $this->nextStatusForApproval($role);
    }

    protected function shouldGenerateAfterApproval(Surat $surat, string $role): bool
    {
        if ($role === self::ROLE_ADMIN) {
            return ! $surat->requiresFinalApproval();
        }

        return $surat->finalApprovalRoleSlug() === $role;
    }
}
