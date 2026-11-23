<?php

namespace App\Models;

use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class User extends Authenticatable implements FilamentUser
{
    use HasFactory, Notifiable;

    // ... isi bawaan (fillable, hidden, casts) tetap ...

    public function canAccessPanel(Panel $panel): bool
    {
        return true; // hanya admin yang punya akun, jadi semua user boleh masuk
    }
}