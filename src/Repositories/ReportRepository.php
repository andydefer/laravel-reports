<?php

declare(strict_types=1);

namespace AndyDefer\LaravelReports\Repositories;

use AndyDefer\DomainStructures\Abstracts\AbstractRecord;
use AndyDefer\LaravelReports\Contracts\Repositories\ReportRepositoryInterface;
use AndyDefer\LaravelReports\Models\Report;
use AndyDefer\LaravelReports\Records\ReportFilterRecord;
use AndyDefer\LaravelReports\Records\ReportRecord;
use AndyDefer\Repository\AbstractRepository;
use Illuminate\Database\Eloquent\Builder;

final class ReportRepository extends AbstractRepository implements ReportRepositoryInterface
{
    public function __construct()
    {
        parent::__construct(
            modelClass: Report::class,
            recordClass: ReportRecord::class,
        );
    }

    protected function applyFilters(Builder $query, AbstractRecord $filters): void
    {
        if (! $filters instanceof ReportFilterRecord) {
            return;
        }

        if ($filters->reporter_type !== null) {
            $query->where('reporter_type', $filters->reporter_type);
        }

        if ($filters->reporter_id !== null) {
            $query->where('reporter_id', $filters->reporter_id);
        }

        if ($filters->reportable_type !== null) {
            $query->where('reportable_type', $filters->reportable_type);
        }

        if ($filters->reportable_id !== null) {
            $query->where('reportable_id', $filters->reportable_id);
        }

        if ($filters->type !== null) {
            $query->where('type', $filters->type);
        }

        if ($filters->status !== null) {
            $query->where('status', $filters->status);
        }

        if ($filters->only_pending) {
            $query->where('status', 'pending');
        }

        if ($filters->updated_at !== null) {
            $query->where('updated_at', '>=', $filters->updated_at->toDateTimeString());
        }
    }
}
