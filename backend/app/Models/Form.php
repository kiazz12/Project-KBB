<?php

namespace App\Models;

use App\Enums\FormStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Form extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'uuid',
        'user_id',
        'opd_id',
        'title',
        'description',
        'slug',
        'status',
        'data_classification',
        'settings',
        'starts_at',
        'ends_at',
        'max_submissions',
        'require_auth',
        'collect_ip',
        'show_kbb_logo',
        'confirmation_message',
        'limit_one_response',
        'confirmation_type',
        'header_image',
        'theme_color',
        'redirect_url',
    ];

    protected function casts(): array
    {
        return [
            'settings' => 'array',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'require_auth' => 'boolean',
            'collect_ip' => 'boolean',
            'show_kbb_logo' => 'boolean',
            'limit_one_response' => 'boolean',
            'allow_anonymous' => 'boolean',
            'allowed_domains' => 'array',
            'status' => FormStatus::class,
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function fields(): HasMany
    {
        return $this->hasMany(FormField::class)->orderBy('order');
    }

    public function sections(): HasMany
    {
        return $this->hasMany(FormSection::class)->orderBy('order');
    }

    public function getSectionsEnabledAttribute(): bool
    {
        return $this->sections()->exists();
    }

    public function submissions(): HasMany
    {
        return $this->hasMany(FormSubmission::class);
    }

    public function scopePublished($query)
    {
        return $query->where('status', FormStatus::Published->value);
    }

    public function scopeDraft($query)
    {
        return $query->where('status', FormStatus::Draft->value);
    }

    public function scopeClosed($query)
    {
        return $query->where('status', FormStatus::Closed->value);
    }

    public function isExpired(): bool
    {
        return $this->ends_at && $this->ends_at->isPast();
    }

    public function isFull(): bool
    {
        return $this->max_submissions && $this->submissions()->count() >= $this->max_submissions;
    }
}
