<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class AuditService
{
    /**
     * Catat aksi penting ke audit_logs.
     *
     * Pemanggil secara historis mengirim array konteks sebagai argumen kedua;
     * array tersebut kini digabungkan ke new_values (sebelumnya memicu TypeError
     * karena get_class() dipanggil di atas array — lihat temuan REA B-01).
     */
    public static function log(string $action, Model|string|array|null $model = null, ?string $description = null, ?array $oldValues = null, ?array $newValues = null): void
    {
        $auditableType = null;
        $auditableId = null;

        if ($model instanceof Model) {
            $auditableType = $model->getMorphClass();
            $auditableId = $model->getKey();
        } elseif (is_array($model)) {
            // Kompatibilitas mundur: array konteks → simpan sebagai data tambahan.
            $newValues = array_merge($model, $newValues ?? []);
        }

        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => $action,
            'auditable_type' => $auditableType,
            'auditable_id' => $auditableId,
            'description' => $description,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
        ]);
    }
}
