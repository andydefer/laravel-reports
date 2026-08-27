<?php

declare(strict_types=1);

namespace AndyDefer\LaravelReports\Datas;

use AndyDefer\DomainStructures\Abstracts\AbstractData;
use AndyDefer\DomainStructures\Utils\StrictDataObject;
use AndyDefer\PhpVo\ValueObjects\DateTimeVO;

/**
 * Data DTO for Report responses.
 *
 * Used to expose report data in API responses without exposing internal model details.
 * Fields are automatically normalized to camelCase for API consistency.
 *
 * @example
 * $reportData = ReportData::from([
 *     'id' => 1,
 *     'reporter_type' => 'App\\Models\\User',
 *     'reporter_id' => 42,
 *     'reportable_type' => 'App\\Models\\Post',
 *     'reportable_id' => 15,
 *     'type' => 'spam',
 *     'reason' => 'Contenu promotionnel',
 *     'status' => 'pending',
 *     'metadata' => ['ip' => '192.168.1.1'],
 *     'created_at' => '2024-01-15T10:00:00Z',
 * ]);
 */
final class ReportData extends AbstractData
{
    public function __construct(
        public readonly int $id,
        public readonly string $reporterType,
        public readonly int $reporterId,
        public readonly string $reportableType,
        public readonly int $reportableId,
        public readonly string $type,
        public readonly ?string $reason,
        public readonly string $status,
        public readonly ?StrictDataObject $metadata = null,
        public readonly ?DateTimeVO $reviewedAt = null,
        public readonly ?DateTimeVO $createdAt = null,
        public readonly ?DateTimeVO $updatedAt = null,
        public readonly ?DateTimeVO $deletedAt = null,
    ) {}
}
