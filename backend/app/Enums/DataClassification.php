<?php

namespace App\Enums;

enum DataClassification: string
{
    /**
     * Public data - no special restrictions
     * Can be viewed, exported, and shared widely
     */
    case PUBLIC = 'public';

    /**
     * Internal data - limited access
     * Can be viewed by authorized internal users
     * Export requires approval
     * More detailed logging
     */
    case INTERNAL = 'internal';

    /**
     * Sensitive data - strict restrictions
     * Accessed only by necessary personnel
     * Export restricted
     * Maximum security measures
     * Enhanced logging and audit trails
     */
    case SENSITIVE = 'sensitive';

    /**
     * Get description
     */
    public function description(): string
    {
        return match ($this) {
            self::PUBLIC => 'Data publik tanpa pembatasan akses khusus',
            self::INTERNAL => 'Data internal dengan pembatasan akses dan logging lebih detail',
            self::SENSITIVE => 'Data sensitif dengan pembatasan akses maksimal dan audit ketat',
        };
    }

    /**
     * Get access restrictions
     */
    public function accessLevel(): int
    {
        return match ($this) {
            self::PUBLIC => 0,      // No restrictions
            self::INTERNAL => 1,    // Limited access
            self::SENSITIVE => 2,   // Strict access
        };
    }

    /**
     * Can this classification be exported?
     */
    public function canExport(): bool
    {
        return match ($this) {
            self::PUBLIC => true,
            self::INTERNAL => true,    // With approval/logging
            self::SENSITIVE => false,  // Export not allowed
        };
    }

    /**
     * Should submission data be masked in logs/dashboards?
     */
    public function shouldMaskInLogs(): bool
    {
        return $this->accessLevel() > 0;
    }

    /**
     * Default retention period in days
     */
    public function retentionDays(): ?int
    {
        return match ($this) {
            self::PUBLIC => null,      // Indefinite
            self::INTERNAL => 365,     // 1 year
            self::SENSITIVE => 90,     // 3 months
        };
    }
}
