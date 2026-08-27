<?php

// src/Contracts/Services/ReportServiceInterface.php

declare(strict_types=1);

namespace AndyDefer\LaravelReports\Contracts\Services;

use AndyDefer\DomainStructures\Utils\StrictDataObject;
use AndyDefer\PhpVo\ValueObjects\DateTimeVO;
use AndyDefer\Repository\Contracts\EnumerableInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use RuntimeException;

/**
 * Interface for the Report service.
 *
 * Defines the contract for managing reports on models.
 */
interface ReportServiceInterface
{
    /**
     * Report a model.
     *
     * @param  Model  $reporter  The user reporting
     * @param  Model  $reportable  The object being reported
     * @param  EnumerableInterface  $type  The type of report
     * @param  EnumerableInterface  $status  The status of the report
     * @param  string  $reason  The reason for the report
     * @param  StrictDataObject|null  $metadata  Additional metadata
     * @return Model The created report
     *
     * @throws RuntimeException If already reported
     */
    public function report(
        Model $reporter,
        Model $reportable,
        EnumerableInterface $type,
        EnumerableInterface $status,
        string $reason,
        ?StrictDataObject $metadata = null,
    ): Model;

    /**
     * Check if a user has already reported a model.
     *
     * @param  Model  $reporter  The user
     * @param  Model  $reportable  The object
     * @return bool True if already reported
     */
    public function hasReported(Model $reporter, Model $reportable): bool;

    /**
     * Get all reports for a model.
     *
     * @param  Model  $reportable  The object
     * @param  bool  $onlyPending  Only pending reports
     * @return Collection Collection of reports
     */
    public function getReportsFor(Model $reportable, bool $onlyPending = false): Collection;

    /**
     * Get all reports by a user.
     *
     * @param  Model  $reporter  The user
     * @return Collection Collection of reports
     */
    public function getReportsBy(Model $reporter): Collection;

    /**
     * Get all pending reports.
     *
     * @return Collection Collection of pending reports
     */
    public function getPendingReports(): Collection;

    /**
     * Get reports by status.
     *
     * @param  EnumerableInterface  $status  The status enum
     * @return Collection Collection of reports
     */
    public function getReportsByStatus(EnumerableInterface $status): Collection;

    /**
     * Get reports by type.
     *
     * @param  EnumerableInterface  $type  The type enum
     * @return Collection Collection of reports
     */
    public function getReportsByType(EnumerableInterface $type): Collection;

    /**
     * Get reports updated after a date.
     *
     * @param  DateTimeVO  $date  The date
     * @return Collection Collection of reports
     */
    public function getReportsUpdatedAfter(DateTimeVO $date): Collection;

    /**
     * Find a report by ID.
     *
     * @param  int  $id  Report ID
     * @return Model|null The report or null
     */
    public function find(int $id): ?Model;

    /**
     * Update report status.
     *
     * @param  int  $id  Report ID
     * @param  EnumerableInterface  $status  New status
     * @return Model The updated report
     *
     * @throws RuntimeException If report not found
     */
    public function updateStatus(int $id, EnumerableInterface $status): Model;

    /**
     * Update report type.
     *
     * @param  int  $id  Report ID
     * @param  EnumerableInterface  $type  New type
     * @return Model The updated report
     *
     * @throws RuntimeException If report not found
     */
    public function updateType(int $id, EnumerableInterface $type): Model;

    /**
     * Count reports for a model.
     *
     * @param  Model  $reportable  The object
     * @param  bool  $onlyPending  Only pending reports
     * @return int Number of reports
     */
    public function countReports(Model $reportable, bool $onlyPending = false): int;

    /**
     * Count reports by status.
     *
     * @param  EnumerableInterface  $status  The status enum
     * @return int Number of reports
     */
    public function countByStatus(EnumerableInterface $status): int;

    /**
     * Count reports by type.
     *
     * @param  EnumerableInterface  $type  The type enum
     * @return int Number of reports
     */
    public function countByType(EnumerableInterface $type): int;

    /**
     * Delete a report.
     *
     * @param  int  $id  Report ID
     *
     * @throws RuntimeException If report not found
     */
    public function delete(int $id): void;
}
