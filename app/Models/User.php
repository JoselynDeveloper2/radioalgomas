<?php

declare(strict_types=1);

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'bio',
        'avatar',
        'website',
        'twitter',
        'linkedin',
        'is_active',
        'role'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
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
            'is_active' => 'boolean'
        ];
    }

    // Roles de usuario
    const ROLE_ADMIN = 'admin';
    const ROLE_EDITOR = 'editor';
    const ROLE_AUTHOR = 'author';
    const ROLE_SUBSCRIBER = 'subscriber';

    public static function getRoles(): array
    {
        return [
            self::ROLE_ADMIN => 'Administrador',
            self::ROLE_EDITOR => 'Editor',
            self::ROLE_AUTHOR => 'Autor',
            self::ROLE_SUBSCRIBER => 'Suscriptor'
        ];
    }

    // Relaciones
    public function articles(): HasMany
    {
        return $this->hasMany(Article::class);
    }

    public function publishedArticles(): HasMany
    {
        return $this->hasMany(Article::class)
                    ->where('status', Article::STATUS_PUBLISHED)
                    ->where('published_at', '<=', now())
                    ->orderBy('published_at', 'desc');
    }

    public function draftArticles(): HasMany
    {
        return $this->hasMany(Article::class)
                    ->where('status', Article::STATUS_DRAFT)
                    ->orderBy('updated_at', 'desc');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeAuthors($query)
    {
        return $query->whereIn('role', [self::ROLE_AUTHOR, self::ROLE_EDITOR, self::ROLE_ADMIN]);
    }

    public function scopeWithArticleCount($query)
    {
        return $query->withCount(['publishedArticles']);
    }

    /**
     * Get the user's initials
     */
    public function initials(): string
    {
        return Str::of($this->name)
            ->explode(' ')
            ->take(2)
            ->map(fn ($word) => Str::substr($word, 0, 1))
            ->implode('');
    }

    // Accessors
    public function getRoleLabelAttribute(): string
    {
        return self::getRoles()[$this->role] ?? 'Desconocido';
    }

    public function getAvatarUrlAttribute(): string
    {
        if ($this->avatar) {
            return url($this->avatar);
        }
        
        return 'https://ui-avatars.com/api/?name=' . urlencode($this->name) . '&color=7F9CF5&background=EBF4FF';
    }

    public function getArticlesCountAttribute(): int
    {
        return $this->articles()->count();
    }

    public function getPublishedArticlesCountAttribute(): int
    {
        return $this->publishedArticles()->count();
    }

    public function getProfileUrlAttribute(): string
    {
        return url('/autor/' . Str::slug($this->name));
    }

    // Métodos de utilidad
    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    public function isEditor(): bool
    {
        return $this->role === self::ROLE_EDITOR;
    }

    public function isAuthor(): bool
    {
        return $this->role === self::ROLE_AUTHOR;
    }

    public function canWriteArticles(): bool
    {
        return in_array($this->role, [self::ROLE_ADMIN, self::ROLE_EDITOR, self::ROLE_AUTHOR]);
    }

    public function canEditAllArticles(): bool
    {
        return in_array($this->role, [self::ROLE_ADMIN, self::ROLE_EDITOR]);
    }

    public function canPublishArticles(): bool
    {
        return in_array($this->role, [self::ROLE_ADMIN, self::ROLE_EDITOR]);
    }

    public function isActive(): bool
    {
        return $this->is_active;
    }

    public function hasArticles(): bool
    {
        return $this->articles()->exists();
    }

    public function hasPublishedArticles(): bool
    {
        return $this->publishedArticles()->exists();
    }
}
