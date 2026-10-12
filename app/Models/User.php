<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
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

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * Check if user is a staff member.
     */
    public function isStaff()
    {
        return $this->role === 'staff';
    }

    /**
     * Check if user is a supervisor.
     */
    public function isSupervisor()
    {
        return $this->role === 'supervisor';
    }

    /**
     * Get contracts created by this user.
     */
    public function contracts()
    {
        return $this->hasMany(Contract::class, 'created_by');
    }
}
