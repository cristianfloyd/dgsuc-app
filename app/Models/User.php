<?php

namespace App\Models;

use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasAvatar;
use Filament\Panel;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Jetstream\HasProfilePhoto;
use Laravel\Sanctum\HasApiTokens;
use Override;

class User extends Authenticatable implements FilamentUser, HasAvatar
{
    use HasApiTokens;
    use HasFactory;
    use HasProfilePhoto;
    use Notifiable;
    use TwoFactorAuthenticatable;

    /**
     * Los atributos que son asignables masivamente.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'username',
        'email',
        'password',
        'is_admin',
        'image',
        'microsoft_id',
        'avatar',
        'office_groups',
    ];

    /**
     * Los atributos que deben ocultarse para la serialización.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_recovery_codes',
        'two_factor_secret',
    ];

    /**
     * Los accesores que se agregarán al formulario de array del modelo.
     *
     * @var array<int, string>
     */
    protected $appends = [
        'profile_photo_url',
    ];

    /**
     * @inheritDoc
     */
    public function canAccessPanel(Panel $panel): bool
    {
        return true;
    }

    /**
     * @inheritDoc
     */
    public function getFilamentAvatarUrl(): ?string
    {
        return $this->profile_photo_path;
    }

    #[\Illuminate\Database\Eloquent\Attributes\Scope]
    protected function search($query, string $val)
    {
        return $query->where('name', 'like', '%' . $val . '%')
            ->orWhere('email', 'like', '%' . $val . '%');
    }

    /**
     * Obtener los atributos que deben ser convertidos.
     *
     * @return array<string, string>
     */
    #[Override]
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    protected function username(): Attribute
    {
        return Attribute::make(
            get: fn($value) => strtolower((string) $value),
            set: fn($value) => strtolower((string) $value),
        );
    }

    protected function completeName(): Attribute
    {
        return Attribute::make(
            get: fn(): string => "{$this->name} {$this->username}",
        );
    }
}
