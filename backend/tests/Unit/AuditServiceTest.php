<?php

namespace Tests\Unit;

use App\Models\AuditLog;
use App\Models\Form;
use App\Models\User;
use App\Services\AuditService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * REA B-01: AuditService::log historis dipanggil dengan array sebagai
 * argumen kedua, memicu TypeError (get_class(array)). Perbaikan harus
 * menerima Model|string|array|null dan menggabungkan array ke new_values.
 */
class AuditServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_log_with_model_sets_morph_columns(): void
    {
        $user = User::factory()->create();
        $form = Form::create([
            'uuid' => \Illuminate\Support\Str::uuid(),
            'user_id' => $user->id,
            'title' => 'Form Audit',
            'slug' => 'form-audit-'.\Illuminate\Support\Str::random(6),
            'status' => 'draft',
        ]);

        AuditService::log('form.updated', $form, 'Form diperbarui');

        $log = AuditLog::where('action', 'form.updated')->firstOrFail();
        $this->assertSame(Form::class, $log->auditable_type);
        $this->assertSame($form->id, $log->auditable_id);
        $this->assertSame('Form diperbarui', $log->description);
    }

    public function test_log_with_array_does_not_throw_and_merges_to_new_values(): void
    {
        // Pemanggilan historis bawaan: array konteks sebagai argumen kedua.
        AuditService::log('notification.clicked', ['notification_id' => 42, 'form_id' => 7], 'Klik notifikasi', null, ['extra' => 'x']);

        $log = AuditLog::where('action', 'notification.clicked')->firstOrFail();
        $this->assertNull($log->auditable_type);
        $this->assertSame(42, $log->new_values['notification_id']);
        $this->assertSame(7, $log->new_values['form_id']);
        $this->assertSame('x', $log->new_values['extra']);
    }

    public function test_log_with_null_model_is_allowed(): void
    {
        AuditService::log('system.tick');

        $log = AuditLog::where('action', 'system.tick')->firstOrFail();
        $this->assertNull($log->auditable_type);
        $this->assertNull($log->auditable_id);
    }
}
