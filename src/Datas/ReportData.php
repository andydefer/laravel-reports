<?php

declare(strict_types=1);

namespace AndyDefer\LaravelReports\Datas;

use AndyDefer\DomainStructures\Abstracts\AbstractData;
use AndyDefer\DomainStructures\Utils\StrictDataObject;
use AndyDefer\PhpVo\ValueObjects\DateTimeZuluVO;

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
 *     'reporter_id' => '550e8400-e29b-41d4-a716-446655440000',
 *     'reportable_type' => 'App\\Models\\Drug',
 *     'reportable_id' => '550e8400-e29b-41d4-a716-446655440001',
 *     'type' => 'counterfeit',
 *     'reason' => 'Médicament contrefait',
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
        public readonly string $reporterId,
        public readonly string $reportableType,
        public readonly string $reportableId,
        public readonly string $type,
        public readonly ?string $reason,
        public readonly string $status,
        public readonly ?StrictDataObject $metadata = null,
        public readonly ?DateTimeZuluVO $reviewedAt = null,
        public readonly ?DateTimeZuluVO $createdAt = null,
        public readonly ?DateTimeZuluVO $updatedAt = null,
        public readonly ?DateTimeZuluVO $deletedAt = null,
    ) {}
}
