<?php

namespace App\Models;

use App\Enums\FieldType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FormField extends Model
{
    protected $fillable = [
        'form_id',
        'section_id',
        'type',
        'label',
        'placeholder',
        'help_text',
        'required',
        'options',
        'order',
        'min_length',
        'max_length',
        'default_value',
        'conditions',
        'allow_other',
        'formula',
        'is_admin_only',
    ];

    protected function casts(): array
    {
        return [
            'options' => 'array',
            'required' => 'boolean',
            'type' => FieldType::class,
            'formula' => 'array',
            'is_admin_only' => 'boolean',
        ];
    }

    public function form(): BelongsTo
    {
        return $this->belongsTo(Form::class);
    }

    public function section(): BelongsTo
    {
        return $this->belongsTo(FormSection::class, 'section_id');
    }

    public function submissionData(): HasMany
    {
        return $this->hasMany(SubmissionData::class);
    }
}
