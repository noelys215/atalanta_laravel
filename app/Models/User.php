<?php

namespace App\Models;

use Filament\Models\Contracts\FilamentUser;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\HasApiTokens;
use Filament\Panel;

class User extends Authenticatable implements FilamentUser
{
    use Notifiable, HasApiTokens;
    use HasFactory;

    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'telephone',
        'country',
        'address',
        'address_cont',
        'state',
        'city',
        'postal_code',
        'password',
        'is_admin'
    ];

    protected $hidden = [
        'password',
        'remember_token'
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'is_admin' => 'boolean',
    ];

    // Mutator for password hashing
    public function setPasswordAttribute($value)
    {
        $this->attributes['password'] = Hash::make($value);
    }

    // Method to check if entered password matches the stored password
    public function matchPassword($enteredPassword)
    {
        return Hash::check($enteredPassword, $this->password);
    }

    // Accessor to get full name
    public function getNameAttribute()
    {
        return "{$this->first_name} {$this->last_name}";
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return $this->is_admin;
    }
}


