<?php

namespace App\Models;

use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasAvatar;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Jetstream\HasProfilePhoto;
use Laravel\Sanctum\HasApiTokens;

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

    public function scopeSearch($query, $val)
    {
        return $query->where('name', 'like', '%' . $val . '%')
            ->orWhere('email', 'like', '%' . $val . '%');
    }

    /**
     * @inheritDoc
     */
    public function canAccessPanel(\Filament\Panel $panel): bool
    {
        return true;
    }

    /**
     * @inheritDoc
     */
    public function getFilamentAvatarUrl(): string|null
    {
        return $this->profile_photo_path;
    }

    /**
     * Obtener los atributos que deben ser convertidos.
     *
     * @return array<string, string>
     */
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
            get: fn ($value) => strtolower($value),
            set: fn ($value) => strtolower($value),
        );
    }

    protected function completeName(): Attribute
    {
        return Attribute::make(
            get: fn () => "{$this->name} {$this->username}",
        );
    }
}
