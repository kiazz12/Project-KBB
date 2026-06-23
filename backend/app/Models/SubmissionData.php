<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SubmissionData extends Model
{
    protected $fillable = [
        'submission_id',
        'form_field_id',
        'value',
    ];

    public function submission(): BelongsTo
    {
        return $this->belongsTo(FormSubmission::class, 'submission_id');
    }

    public function formField(): BelongsTo
    {
        return $this->belongsTo(FormField::class, 'form_field_id');
    }
}
