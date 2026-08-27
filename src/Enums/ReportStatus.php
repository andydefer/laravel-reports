<?php

declare(strict_types=1);

namespace AndyDefer\LaravelReports\Enums;

use AndyDefer\Repository\Contracts\EnumerableInterface;

enum ReportStatus: string implements EnumerableInterface
{
    case PENDING = 'pending';
    case REVIEWED = 'reviewed';
    case DISMISSED = 'dismissed';
    case RESOLVED = 'resolved';

    public function getValue(): string
    {
        return $this->value;
    }

    public function getLabel(): string
    {
        return match ($this) {
            self::PENDING => 'En attente',
            self::REVIEWED => 'Examiné',
            self::DISMISSED => 'Rejeté',
            self::RESOLVED => 'Résolu',
        };
    }

    public function isActive(): bool
    {
        return $this === self::PENDING;
    }

    public function isClosed(): bool
    {
        return in_array($this, [self::DISMISSED, self::RESOLVED]);
    }
}
