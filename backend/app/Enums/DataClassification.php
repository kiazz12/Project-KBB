<?php

namespace App\Enums;

enum DataClassification: string
{
    case PUBLIC = 'public';

    case INTERNAL = 'internal';

    case SENSITIVE = 'sensitive';

    public function description(): string
    {
        return match ($this) {
            self::PUBLIC => 'Data publik tanpa pembatasan akses khusus',
            self::INTERNAL => 'Data internal dengan pembatasan akses dan logging lebih detail',
            self::SENSITIVE => 'Data sensitif dengan pembatasan akses maksimal dan audit ketat',
        };
    }

    public function accessLevel(): int
    {
        return match ($this) {
            self::PUBLIC => 0,
            self::INTERNAL => 1,
            self::SENSITIVE => 2,
        };
    }

    public function canExport(): bool
    {
        return match ($this) {
            self::PUBLIC => true,
            self::INTERNAL => true,
            self::SENSITIVE => false,
        };
    }

    public function shouldMaskInLogs(): bool
    {
        return $this->accessLevel() > 0;
    }

    public function retentionDays(): ?int
    {
        return match ($this) {
            self::PUBLIC => null,
            self::INTERNAL => 365,
            self::SENSITIVE => 90,
        };
    }
}
