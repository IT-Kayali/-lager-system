<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    public const ROLE_ADMIN = 'admin';
    public const ROLE_MANAGER = 'manager';
    public const ROLE_WAREHOUSE = 'warehouse';
    public const ROLE_SALES = 'sales';

    /**
     * Legacy alias so existing tests/code using the former wholesale constant
     * continue to resolve to the new sales role.
     */
    public const ROLE_WHOLESALE = self::ROLE_SALES;

    public const ROLE_LABELS = [
        self::ROLE_ADMIN => 'Admin',
        self::ROLE_MANAGER => 'Manager',
        self::ROLE_WAREHOUSE => 'Lager',
        self::ROLE_SALES => 'Verkauf',
    ];

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    public function hasRole(string|array $roles): bool
    {
        $roles = is_array($roles) ? $roles : [$roles];

        if ($this->isAdmin()) {
            return true;
        }

        return in_array($this->role, $roles, true);
    }

    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    /**
     * Manager privileges include Admin so legacy manager-only UI checks
     * automatically keep working for the new superuser role.
     */
    public function isManager(): bool
    {
        return $this->isAdmin() || $this->role === self::ROLE_MANAGER;
    }

    public function isSales(): bool
    {
        return $this->role === self::ROLE_SALES;
    }

    public function isWholesale(): bool
    {
        return $this->isSales();
    }

    public function isWarehouse(): bool
    {
        return $this->role === self::ROLE_WAREHOUSE;
    }

    public function canAccessMenu(array $roles): bool
    {
        return $this->is_active && $this->hasRole($roles);
    }

    public function roleLabel(): string
    {
        return self::ROLE_LABELS[$this->role] ?? $this->role;
    }

    public function initials(): string
    {
        return Str::of($this->name)
            ->explode(' ')
            ->filter()
            ->take(2)
            ->map(fn (string $name) => Str::of($name)->substr(0, 1))
            ->implode('');
    }
}
