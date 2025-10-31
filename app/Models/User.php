<?php

namespace App\Models;

use Filament\Models\Contracts\FilamentUser;   // ✅ import this
use Filament\Panel;                           // ✅ import this
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements FilamentUser   // ✅ implement the contract
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
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
        ];
    }

    /** Restrict admin panel access */
    public function canAccessPanel(Panel $panel): bool
    {
        return $this->role === 'admin';   // ✅ only admins can log in to /admin
        // or return in_array($this->role, ['admin','staff'], true);
    }
}
