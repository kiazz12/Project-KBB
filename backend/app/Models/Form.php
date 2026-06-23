<?php

namespace App\Models;

use App\Enums\FormStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Form extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'uuid',
        'user_id',
        'title',
        'description',
        'slug',
        'status',
        'settings',
        'starts_at',
        'expires_at',
        'max_submissions',
        'require_auth',
        'collect_ip',
        'show_kbb_logo',
        'confirmation_message',
        'limit_one_response',
        'confirmation_type',
        'allow_anonymous',
        'notification_email',
        'allowed_domains',
        'custom_css',
        'submit_button_text',
        'welcome_message',
        'redirect_url',
    ];

    protected function casts(): array
    {
        return [
            'settings' => 'array',
            'starts_at' => 'datetime',
            'expires_at' => 'datetime',
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
        $date = $this->expires_at ?? $this->ends_at ?? null;
        return $date && $date->isPast();
    }

    public function isFull(): bool
    {
        return $this->max_submissions && $this->submissions()->count() >= $this->max_submissions;
    }
}
