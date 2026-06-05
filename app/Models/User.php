<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, TwoFactorAuthenticatable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role_id',
        'program_studi_id',
        'nim_nip',
        'nomor_induk',
        'no_telepon',
        'foto_path',
        'is_active',
        'status_approval',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'two_factor_confirmed_at' => 'datetime',
            'is_active' => 'boolean',
        ];
    }

    /**
     * @return BelongsTo<Role, $this>
     */
    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    public function hasFastUserRole(): bool
    {
        $slug = str((string) $this->role?->slug)->slug()->toString();
        $name = str((string) $this->role?->nama)->slug()->toString();

        return str($slug)->contains(['mahasiswa', 'dosen'])
            || str($name)->contains(['mahasiswa', 'dosen']);
    }

    public function roleSlug(): string
    {
        $this->loadMissing('role');

        $slug = str((string) $this->role?->slug)->slug()->toString();

        if ($slug !== '') {
            return $slug;
        }

        return str((string) $this->role?->nama)->slug()->toString();
    }

    public function hasRole(string ...$roles): bool
    {
        $roleSlug = $this->roleSlug();

        return in_array($roleSlug, array_map(
            static fn (string $role): string => str($role)->slug()->toString(),
            $roles,
        ), true);
    }
}
