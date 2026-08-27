<?php

declare(strict_types=1);

namespace AndyDefer\LaravelReports\Records;

use AndyDefer\DomainStructures\Abstracts\AbstractRecord;
use AndyDefer\DomainStructures\Utils\StrictDataObject;
use AndyDefer\PhpVo\ValueObjects\DateTimeVO;

final class ReportRecord extends AbstractRecord
{
    public function __construct(
        public readonly ?int $id = null,
        public readonly ?string $reporter_type = null,
        public readonly ?int $reporter_id = null,
        public readonly ?string $reportable_type = null,
        public readonly ?int $reportable_id = null,
        public readonly ?string $type = null,
        public readonly ?string $reason = null,
        public readonly ?StrictDataObject $metadata = null,
        public readonly ?string $status = null,
        public readonly ?DateTimeVO $reviewed_at = null,
        public readonly ?DateTimeVO $created_at = null,
        public readonly ?DateTimeVO $updated_at = null,
        public readonly ?DateTimeVO $deleted_at = null,
    ) {}
}
