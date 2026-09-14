<?php

declare(strict_types=1);

namespace AndyDefer\LaravelReports\Database\Factories;

use AndyDefer\LaravelReports\Enums\ReportStatus;
use AndyDefer\LaravelReports\Models\Report;
use AndyDefer\Repository\Contracts\EnumerableInterface;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;

/**
 * @extends Factory<Report>
 */
final class ReportFactory extends Factory
{
    protected $model = Report::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'reporter_type' => 'App\\Models\\User',
            'reporter_id' => 1,
            'reportable_type' => 'App\\Models\\Drug',
            'reportable_id' => 1,
            'type' => 'other',
            'reason' => $this->faker->sentence(10),
            'status' => ReportStatus::PENDING->value,
            'metadata' => [
                'ip' => $this->faker->ipv4(),
                'user_agent' => $this->faker->userAgent(),
                'source' => $this->faker->randomElement(['web', 'mobile', 'api']),
            ],
            'reviewed_at' => null,
        ];
    }

    /**
     * Set a specific report type.
     */
    public function type(EnumerableInterface $type): self
    {
        return $this->state(['type' => $type->getValue()]);
    }

    /**
     * Set a specific report status.
     */
    public function status(EnumerableInterface $status): self
    {
        return $this->state(['status' => $status->getValue()]);
    }

    /**
     * Set the report as pending.
     */
    public function pending(): self
    {
        return $this->state([
            'status' => ReportStatus::PENDING->value,
            'reviewed_at' => null,
        ]);
    }

    /**
     * Set the report as reviewed.
     */
    public function reviewed(): self
    {
        return $this->state([
            'status' => ReportStatus::REVIEWED->value,
            'reviewed_at' => now(),
        ]);
    }

    /**
     * Set the report as dismissed.
     */
    public function dismissed(): self
    {
        return $this->state([
            'status' => ReportStatus::DISMISSED->value,
            'reviewed_at' => now(),
        ]);
    }

    /**
     * Set the report as resolved.
     */
    public function resolved(): self
    {
        return $this->state([
            'status' => ReportStatus::RESOLVED->value,
            'reviewed_at' => now(),
        ]);
    }

    /**
     * Set the reporter (the entity creating the report).
     */
    public function reporter(Model $model): self
    {
        return $this->state([
            'reporter_type' => $model->getMorphClass(),
            'reporter_id' => $model->getKey(),
        ]);
    }

    /**
     * Set the reportable (the entity being reported).
     */
    public function reportable(Model $model): self
    {
        return $this->state([
            'reportable_type' => $model->getMorphClass(),
            'reportable_id' => $model->getKey(),
        ]);
    }

    /**
     * Set the reason for the report.
     */
    public function reason(string $reason): self
    {
        return $this->state(['reason' => $reason]);
    }

    /**
     * Set custom metadata.
     *
     * @param  array<string, mixed>  $metadata
     */
    public function withMetadata(array $metadata): self
    {
        return $this->state(['metadata' => $metadata]);
    }

    /**
     * Create a report without metadata.
     */
    public function withoutMetadata(): self
    {
        return $this->state(['metadata' => null]);
    }

    /**
     * Create a report from a specific source.
     */
    public function fromSource(string $source): self
    {
        return $this->state(function (array $attributes) use ($source) {
            $metadata = $attributes['metadata'] ?? [];
            $metadata['source'] = $source;

            return ['metadata' => $metadata];
        });
    }

    /**
     * Create a report with random metadata.
     */
    public function withRandomMetadata(): self
    {
        return $this->state(fn () => [
            'metadata' => [
                'ip' => $this->faker->ipv4(),
                'user_agent' => $this->faker->userAgent(),
                'source' => $this->faker->randomElement(['web', 'mobile', 'api']),
                'session_id' => $this->faker->uuid(),
                'timestamp' => $this->faker->dateTime()->format('Y-m-d H:i:s'),
            ],
        ]);
    }

    /**
     * Create a report with a long reason.
     */
    public function withLongReason(): self
    {
        return $this->state([
            'reason' => $this->faker->paragraph(5),
        ]);
    }

    /**
     * Create a report with a short reason.
     */
    public function withShortReason(): self
    {
        return $this->state([
            'reason' => $this->faker->sentence(3),
        ]);
    }

    /**
     * Create a soft-deleted report.
     */
    public function trashed(): self
    {
        return $this->state(['deleted_at' => now()]);
    }

    /**
     * Create a report with all fields explicitly set.
     *
     * @param  array<string, mixed>  $metadata
     */
    public function complete(
        Model $reporter,
        Model $reportable,
        EnumerableInterface $type,
        EnumerableInterface $status,
        string $reason,
        array $metadata = []
    ): self {
        return $this->state([
            'reporter_type' => $reporter->getMorphClass(),
            'reporter_id' => $reporter->getKey(),
            'reportable_type' => $reportable->getMorphClass(),
            'reportable_id' => $reportable->getKey(),
            'type' => $type->getValue(),
            'status' => $status->getValue(),
            'reason' => $reason,
            'metadata' => $metadata,
        ]);
    }
}
