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
    ];

    protected function casts(): array
    {
        return [
            'options' => 'array',
            'required' => 'boolean',
            'type' => FieldType::class,
        ];
    }

    public function form(): BelongsTo
    {
        return $this->belongsTo(Form::class);
    }

    public function submissionData(): HasMany
    {
        return $this->hasMany(SubmissionData::class);
    }
}
