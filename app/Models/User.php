<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

#[Fillable(['name', 'email', 'password', 'avatar', 'institution', 'department', 'bio', 'last_active_at'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, HasRoles, Notifiable, SoftDeletes;

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_active_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function uploadedResources(): HasMany
    {
        return $this->hasMany(Resource::class, 'uploader_id');
    }

    public function collections(): HasMany
    {
        return $this->hasMany(Collection::class, 'created_by');
    }

    public function downloads(): HasMany
    {
        return $this->hasMany(Download::class);
    }

    public function readingProgress(): HasMany
    {
        return $this->hasMany(ReadingProgress::class);
    }

    public function bookmarks(): HasMany
    {
        return $this->hasMany(Bookmark::class);
    }

    public function highlights(): HasMany
    {
        return $this->hasMany(Highlight::class);
    }

    public function notes(): HasMany
    {
        return $this->hasMany(Note::class);
    }

    public function favoriteResources(): BelongsToMany
    {
        return $this->belongsToMany(Resource::class, 'user_favorites');
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return $this->hasRole('administrator');
    }
}
