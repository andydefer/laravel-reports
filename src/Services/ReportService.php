<?php

// src/Services/ReportService.php

declare(strict_types=1);

namespace AndyDefer\LaravelReports\Services;

use AndyDefer\DomainStructures\Utils\StrictDataObject;
use AndyDefer\LaravelReports\Contracts\Repositories\ReportRepositoryInterface;
use AndyDefer\LaravelReports\Contracts\Services\ReportServiceInterface;
use AndyDefer\LaravelReports\Records\ReportFilterRecord;
use AndyDefer\LaravelReports\Records\ReportRecord;
use AndyDefer\PhpVo\ValueObjects\DateTimeVO;
use AndyDefer\Repository\Contracts\EnumerableInterface;
use AndyDefer\Repository\Records\FindByRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use RuntimeException;

/**
 * Service for managing reports.
 *
 * @implements ReportServiceInterface
 */
final class ReportService implements ReportServiceInterface
{
    public function __construct(
        private readonly ReportRepositoryInterface $repository,
    ) {}

    /**
     * {@inheritDoc}
     */
    public function report(
        Model $reporter,
        Model $reportable,
        EnumerableInterface $type,
        EnumerableInterface $status,
        string $reason,
        ?StrictDataObject $metadata = null,
    ): Model {
        if ($this->hasReported($reporter, $reportable)) {
            throw new RuntimeException('Vous avez déjà signalé ce contenu.');
        }

        $record = ReportRecord::from([
            'reporter_type' => $reporter->getMorphClass(),
            'reporter_id' => $reporter->getKey(),
            'reportable_type' => $reportable->getMorphClass(),
            'reportable_id' => $reportable->getKey(),
            'type' => $type,
            'status' => $status,
            'reason' => $reason,
            'metadata' => $metadata,
        ]);

        return $this->repository->create($record);
    }

    /**
     * {@inheritDoc}
     */
    public function hasReported(Model $reporter, Model $reportable): bool
    {
        $filter = ReportFilterRecord::from([
            'reporter_type' => $reporter->getMorphClass(),
            'reporter_id' => $reporter->getKey(),
            'reportable_type' => $reportable->getMorphClass(),
            'reportable_id' => $reportable->getKey(),
        ]);

        return $this->repository->count($filter) > 0;
    }

    /**
     * {@inheritDoc}
     */
    public function getReportsFor(Model $reportable, bool $onlyPending = false): Collection
    {
        $filter = ReportFilterRecord::from([
            'reportable_type' => $reportable->getMorphClass(),
            'reportable_id' => $reportable->getKey(),
            'only_pending' => $onlyPending,
        ]);

        $findBy = new FindByRecord(filters: $filter);

        return $this->repository->findBy($findBy);
    }

    /**
     * {@inheritDoc}
     */
    public function getReportsBy(Model $reporter): Collection
    {
        $filter = ReportFilterRecord::from([
            'reporter_type' => $reporter->getMorphClass(),
            'reporter_id' => $reporter->getKey(),
        ]);

        $findBy = new FindByRecord(filters: $filter);

        return $this->repository->findBy($findBy);
    }

    /**
     * {@inheritDoc}
     */
    public function getPendingReports(): Collection
    {
        $filter = ReportFilterRecord::from([
            'only_pending' => true,
        ]);

        $findBy = new FindByRecord(filters: $filter);

        return $this->repository->findBy($findBy);
    }

    /**
     * {@inheritDoc}
     */
    public function getReportsByStatus(EnumerableInterface $status): Collection
    {
        $filter = ReportFilterRecord::from([
            'status' => $status,
        ]);

        $findBy = new FindByRecord(filters: $filter);

        return $this->repository->findBy($findBy);
    }

    /**
     * {@inheritDoc}
     */
    public function getReportsByType(EnumerableInterface $type): Collection
    {
        $filter = ReportFilterRecord::from([
            'type' => $type,
        ]);

        $findBy = new FindByRecord(filters: $filter);

        return $this->repository->findBy($findBy);
    }

    /**
     * {@inheritDoc}
     */
    public function getReportsUpdatedAfter(DateTimeVO $date): Collection
    {
        $filter = ReportFilterRecord::from([
            'updated_at' => $date,
        ]);

        $findBy = new FindByRecord(filters: $filter);

        return $this->repository->findBy($findBy);
    }

    /**
     * {@inheritDoc}
     */
    public function find(int $id): ?Model
    {
        return $this->repository->find($id);
    }

    /**
     * {@inheritDoc}
     */
    public function updateStatus(int $id, EnumerableInterface $status): Model
    {
        $report = $this->find($id);

        if (! $report) {
            throw new RuntimeException(sprintf('Report %d not found', $id));
        }

        $record = ReportRecord::from(['status' => $status]);

        return $this->repository->update($id, $record);
    }

    /**
     * {@inheritDoc}
     */
    public function updateType(int $id, EnumerableInterface $type): Model
    {
        $report = $this->find($id);

        if (! $report) {
            throw new RuntimeException(sprintf('Report %d not found', $id));
        }

        $record = ReportRecord::from(['type' => $type]);

        return $this->repository->update($id, $record);
    }

    /**
     * {@inheritDoc}
     */
    public function countReports(Model $reportable, bool $onlyPending = false): int
    {
        $filter = ReportFilterRecord::from([
            'reportable_type' => $reportable->getMorphClass(),
            'reportable_id' => $reportable->getKey(),
            'only_pending' => $onlyPending,
        ]);

        return $this->repository->count($filter);
    }

    /**
     * {@inheritDoc}
     */
    public function countByStatus(EnumerableInterface $status): int
    {
        $filter = ReportFilterRecord::from([
            'status' => $status,
        ]);

        return $this->repository->count($filter);
    }

    /**
     * {@inheritDoc}
     */
    public function countByType(EnumerableInterface $type): int
    {
        $filter = ReportFilterRecord::from([
            'type' => $type,
        ]);

        return $this->repository->count($filter);
    }

    /**
     * {@inheritDoc}
     */
    public function delete(int $id): void
    {
        $report = $this->find($id);

        if (! $report) {
            throw new RuntimeException(sprintf('Report %d not found', $id));
        }

        $this->repository->delete($id);
    }
}
