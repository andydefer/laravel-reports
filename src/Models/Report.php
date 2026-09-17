<?php

declare(strict_types=1);

namespace AndyDefer\LaravelReports\Models;

use AndyDefer\DomainStructures\Utils\StrictDataObject;
use AndyDefer\LaravelRattachments\Contracts\RattachmentInterface;
use AndyDefer\LaravelRattachments\Traits\HasRattachments;
use AndyDefer\LaravelReports\Database\Factories\ReportFactory;
use AndyDefer\PhpVo\ValueObjects\DateTimeVO;
use AndyDefer\Repository\Casts\EnumCast;
use AndyDefer\Repository\Contracts\EnumerableInterface;
use AndyDefer\Repository\Proxies\AttributeProxy;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * Report model representing user reports.
 *
 * @property int $id
 * @property string $reporter_type
 * @property int $reporter_id
 * @property string $reportable_type
 * @property int $reportable_id
 * @property EnumerableInterface $type
 * @property string|null $reason
 * @property EnumerableInterface $status
 * @property StrictDataObject|null $metadata
 * @property DateTimeVO|null $reviewed_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @property-read Model|null $reporter
 * @property-read Model|null $reportable
 * @property-read \UnitEnum|null $type_enum
 * @property-read \UnitEnum|null $status_enum
 */
final class Report extends Model implements RattachmentInterface
{
    /** @use HasFactory<ReportFactory> */
    use HasFactory;

    use HasRattachments;
    use SoftDeletes;

    protected $table = 'reports';

    protected $fillable = [
        'reporter_type',
        'reporter_id',
        'reportable_type',
        'reportable_id',
        'type',
        'reason',
        'metadata',
        'status',
        'reviewed_at',
    ];

    protected $casts = [
        'type' => EnumCast::class,
        'status' => EnumCast::class,
        'metadata' => 'array',
        'reviewed_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Resolve the factory used by the model.
     */
    protected static function newFactory(): Factory
    {
        return ReportFactory::new();
    }

    // ============ Relations ============

    public function reporter()
    {
        return $this->morphTo();
    }

    public function reportable()
    {
        return $this->morphTo();
    }

    // ============ Attributes ============

    protected function metadata(): Attribute
    {
        return AttributeProxy::nullable(
            StrictDataObject::class,
            column: 'metadata',
        );
    }

    protected function reviewedAt(): Attribute
    {
        return AttributeProxy::nullable(
            DateTimeVO::class,
            column: 'reviewed_at',
        );
    }

    // ==========================================================================
    // RATTACHMENT CONSTRAINTS INTERFACE
    // ==========================================================================

    /**
     * Define the targets this model is allowed to attach to.
     *
     * @return array<class-string, array<EnumerableInterface>>
     */
    public function allowedTargets(): array
    {
        return [];
    }

    /**
     * Define the unique target constraints for this model.
     *
     * @return array<class-string, array<EnumerableInterface>>
     */
    public function uniqueTargets(): array
    {
        return [];
    }

    /**
     * Define the targets this model is forbidden from attaching to.
     *
     * @return array<class-string, array<EnumerableInterface>>
     */
    public function disallowedTargets(): array
    {
        return [];
    }
}
