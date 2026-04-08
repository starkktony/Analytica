<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;


    protected $fillable = [
        'name',
        'email',
        'role',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
        ];
    }

    /**
     * Convenience helpers — use in Blade with auth()->user()->isAdmin() etc.
     */
    public function isAdmin(): bool
    {
        return $this->role === 'Admin';
    }

    public function isExecutive(): bool
    {
        return $this->role === 'Executive';
    }

    public function isDirector(): bool
    {
        return $this->role === 'Director';
    }

    public function isChief(): bool
    {
        return $this->role === 'Chief';
    }

    public function isTeaching(): bool
    {
        return $this->role === 'Employee-Teaching';
    }

    public function isNonTeaching(): bool
    {
        return $this->role === 'Employee-Non-Teaching';
    }

    /**
     * Check if this user can access a given route name prefix.
     */
    public function canAccess(string $routePrefix): bool
    {
        $map = [
            'Admin'                  => ['*'],
            'Executive'              => ['*'],
            'Director'               => ['dashboard', 'student', 'graduates', 'normative-funding', 'faculty', 'radiis'],
            'Chief'                  => ['dashboard', 'student', 'graduates', 'normative-funding', 'faculty', 'radiis'],
            'Employee-Teaching'      => ['dashboard', 'faculty'],
            'Employee-Non-Teaching'  => ['dashboard'],
        ];

        $allowed = $map[$this->role] ?? [];
        if (in_array('*', $allowed)) return true;

        foreach ($allowed as $prefix) {
            if (str_starts_with($routePrefix, $prefix)) return true;
        }

        return false;
    }
}