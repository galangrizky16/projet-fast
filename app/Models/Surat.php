<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Cache;
use InvalidArgumentException;
use Illuminate\Support\Str;
use App\Models\SuratQrCode;

class Surat extends Model
{
    public const STATUS_PENDING = 'pending';

    public const STATUS_VALIDATED_ADMIN = 'validated_admin';

    public const STATUS_APPROVED_KAPRODI = 'approved_kaprodi';

    public const STATUS_APPROVED_DEKAN = 'approved_dekan';

    public const STATUS_FINISHED = 'finished';

    public const STATUS_REJECTED = 'rejected';

    public const STATUS_CANCELLED = 'cancelled';

    public const WORKFLOW_STATUSES = [
        self::STATUS_PENDING,
        self::STATUS_VALIDATED_ADMIN,
        self::STATUS_APPROVED_KAPRODI,
        self::STATUS_APPROVED_DEKAN,
        self::STATUS_FINISHED,
        self::STATUS_REJECTED,
    ];

    protected $fillable = [
        'jenis_surat_id',
        'pemohon_id',
        'type',
        'nomor_surat',
        'keperluan',
        'status',
        'isi_surat',
        'generated_file_path',
        'generated_file_type',
        'generated_at',
        'template_version',
        'rendered_snapshot',
        'tanggal_pengajuan',
        'tanggal_kebutuhan',
        'tanggal_selesai',
        'qr_token',
        'generated_by',
        'qr_validated_at',
        'admin_note',
        'rejection_reason',
        'validated_by_admin_id',
        'validated_by_admin_at',
        'approved_by_id',
        'approved_at',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_pengajuan' => 'datetime',
            'tanggal_kebutuhan' => 'date',
            'tanggal_selesai' => 'datetime',
            'generated_at' => 'datetime',
            'qr_validated_at' => 'datetime',
            'validated_by_admin_at' => 'datetime',
            'approved_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::saved(function (self $surat): void {
            if ($surat->wasRecentlyCreated || $surat->wasChanged('status')) {
                Cache::forget('notif_count_pending_admin');
            }
        });
    }

    public function qrCode(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(SuratQrCode::class)->latestOfMany();
    }

    /**
     * @return BelongsTo<JenisSurat, $this>
     */
    public function jenisSurat(): BelongsTo
    {
        return $this->belongsTo(JenisSurat::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function pemohon(): BelongsTo
    {
        return $this->belongsTo(User::class, 'pemohon_id');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function validatedByAdmin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'validated_by_admin_id');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by_id');
    }

    /**
     * @return HasMany<SuratLampiran, $this>
     */
    public function lampirans(): HasMany
    {
        return $this->hasMany(SuratLampiran::class);
    }

    /**
     * @return HasMany<SuratData, $this>
     */
    public function dataEntries(): HasMany
    {
        return $this->hasMany(SuratData::class);
    }

    /**
     * @return HasMany<SuratApprovalFlow, $this>
     */
    public function approvalFlows(): HasMany
    {
        return $this->hasMany(SuratApprovalFlow::class)->orderBy('urutan')->orderBy('id');
    }

    public function canBeValidatedByAdmin(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function canBeRejectedByAdmin(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function canBeApprovedByRole(string $role): bool
    {
        $finalApprovalRole = $this->finalApprovalRoleSlug();

        return match ($role) {
            'admin' => $this->status === self::STATUS_PENDING,
            'kaprodi' => $this->status === self::STATUS_VALIDATED_ADMIN && $finalApprovalRole === 'kaprodi',
            'dekan' => $this->status === self::STATUS_VALIDATED_ADMIN && $finalApprovalRole === 'dekan',
            default => throw new InvalidArgumentException('Role approval tidak dikenali.'),
        };
    }

    public function canBeRejectedByRole(string $role): bool
    {
        $finalApprovalRole = $this->finalApprovalRoleSlug();

        return match ($role) {
            'admin' => $this->status === self::STATUS_PENDING,
            'kaprodi' => $this->status === self::STATUS_VALIDATED_ADMIN && $finalApprovalRole === 'kaprodi',
            'dekan' => $this->status === self::STATUS_VALIDATED_ADMIN && $finalApprovalRole === 'dekan',
            default => throw new InvalidArgumentException('Role approval tidak dikenali.'),
        };
    }

    public function finalApprovalRoleSlug(): ?string
    {
        $this->loadMissing('jenisSurat.approvalRole');

        $slug = (string) ($this->jenisSurat?->approvalRole?->slug ?? '');
        $name = (string) ($this->jenisSurat?->approvalRole?->nama ?? '');

        $normalizedSlug = Str::slug($slug);
        $normalizedName = Str::slug($name);

        if (Str::contains($normalizedSlug, 'kaprodi') || Str::contains($normalizedName, 'kaprodi')) {
            return 'kaprodi';
        }

        if (Str::contains($normalizedSlug, 'dekan') || Str::contains($normalizedName, 'dekan')) {
            return 'dekan';
        }

        return null;
    }

    public function requiresFinalApproval(): bool
    {
        $this->loadMissing('jenisSurat');

        return (bool) ($this->jenisSurat?->perlu_approval ?? false)
            && $this->finalApprovalRoleSlug() !== null;
    }
}
