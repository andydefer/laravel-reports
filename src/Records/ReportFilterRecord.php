<?php

declare(strict_types=1);

namespace AndyDefer\LaravelReports\Records;

use AndyDefer\DomainStructures\Abstracts\AbstractRecord;
use AndyDefer\PhpVo\ValueObjects\DateTimeVO;

final class ReportFilterRecord extends AbstractRecord
{
    public function __construct(
        public readonly ?string $reporter_type = null,
        public readonly ?int $reporter_id = null,
        public readonly ?string $reportable_type = null,
        public readonly ?int $reportable_id = null,
        public readonly ?string $type = null,
        public readonly ?string $status = null,
        public readonly ?DateTimeVO $updated_at = null,
        public readonly ?bool $only_pending = false,
    ) {}
}
