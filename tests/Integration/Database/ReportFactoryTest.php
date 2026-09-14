<?php

declare(strict_types=1);

namespace AndyDefer\LaravelReports\Tests\Integration\Database;

use AndyDefer\LaravelReports\Enums\ReportStatus;
use AndyDefer\LaravelReports\Enums\ReportType;
use AndyDefer\LaravelReports\Models\Report;
use AndyDefer\LaravelReports\Tests\Fixtures\Models\TestPost;
use AndyDefer\LaravelReports\Tests\Fixtures\Models\TestUser;
use AndyDefer\LaravelReports\Tests\IntegrationTestCase;

final class ReportFactoryTest extends IntegrationTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->app['config']->set('repository.enum_casts', [
            'reports' => [
                'type' => ReportType::class,
                'status' => ReportStatus::class,
            ],
        ]);
    }

    public function test_it_creates_a_report_with_default_state(): void
    {
        // Act
        $report = Report::factory()->create();

        // Assert
        $this->assertInstanceOf(Report::class, $report);
        $this->assertDatabaseHas('reports', [
            'id' => $report->id,
            'reporter_type' => 'App\\Models\\User',
            'reporter_id' => 1,
            'reportable_type' => 'App\\Models\\Drug',
            'reportable_id' => 1,
            'type' => 'other',
            'status' => ReportStatus::PENDING->value,
        ]);
    }

    public function test_it_sets_reporter_and_reportable(): void
    {
        // Arrange
        $user = TestUser::create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
        ]);

        $post = TestPost::create([
            'user_id' => $user->id,
            'title' => 'Post title',
            'body' => 'Post body',
        ]);

        // Act
        $report = Report::factory()
            ->reporter($user)
            ->reportable($post)
            ->create();

        // Assert
        $this->assertDatabaseHas('reports', [
            'id' => $report->id,
            'reporter_type' => TestUser::class,
            'reporter_id' => $user->id,
            'reportable_type' => TestPost::class,
            'reportable_id' => $post->id,
        ]);
    }

    public function test_it_sets_type_using_enumerable_interface(): void
    {
        // Act
        $report = Report::factory()
            ->type(ReportType::SPAM)
            ->create();

        // Assert
        $this->assertDatabaseHas('reports', [
            'id' => $report->id,
            'type' => ReportType::SPAM->value,
        ]);
    }

    public function test_it_sets_status_using_enumerable_interface(): void
    {
        // Act
        $report = Report::factory()
            ->status(ReportStatus::REVIEWED)
            ->create();

        // Assert
        $this->assertDatabaseHas('reports', [
            'id' => $report->id,
            'status' => ReportStatus::REVIEWED->value,
        ]);
    }

    public function test_pending_state_sets_status_pending(): void
    {
        // Act
        $report = Report::factory()->pending()->create();

        // Assert
        $this->assertEquals(ReportStatus::PENDING->value, $report->status->getValue());
        $this->assertNull($report->reviewed_at);
    }

    public function test_reviewed_state_sets_status_and_reviewed_at(): void
    {
        // Act
        $report = Report::factory()->reviewed()->create();

        // Assert
        $this->assertEquals(ReportStatus::REVIEWED->value, $report->status->getValue());
        $this->assertNotNull($report->reviewed_at);
    }

    public function test_dismissed_state_sets_status_and_reviewed_at(): void
    {
        // Act
        $report = Report::factory()->dismissed()->create();

        // Assert
        $this->assertEquals(ReportStatus::DISMISSED->value, $report->status->getValue());
        $this->assertNotNull($report->reviewed_at);
    }

    public function test_resolved_state_sets_status_and_reviewed_at(): void
    {
        // Act
        $report = Report::factory()->resolved()->create();

        // Assert
        $this->assertEquals(ReportStatus::RESOLVED->value, $report->status->getValue());
        $this->assertNotNull($report->reviewed_at);
    }

    public function test_reason_state_sets_custom_reason(): void
    {
        // Act
        $report = Report::factory()
            ->reason('Custom reason for reporting')
            ->create();

        // Assert
        $this->assertEquals('Custom reason for reporting', $report->reason);
    }

    public function test_with_metadata_state_sets_custom_metadata(): void
    {
        // Arrange
        $metadata = ['custom_key' => 'custom_value', 'source' => 'test'];

        // Act
        $report = Report::factory()
            ->withMetadata($metadata)
            ->create();

        // Assert
        $this->assertEquals('custom_value', $report->metadata?->custom_key);
    }

    public function test_without_metadata_state_sets_null(): void
    {
        // Act
        $report = Report::factory()->withoutMetadata()->create();

        // Assert
        $this->assertNull($report->metadata);
    }

    public function test_from_source_state_updates_metadata_source(): void
    {
        // Act
        $report = Report::factory()
            ->fromSource('mobile')
            ->create();

        // Assert
        $this->assertEquals('mobile', $report->metadata?->source);
    }

    public function test_with_random_metadata_state_sets_full_metadata(): void
    {
        // Act
        $report = Report::factory()->withRandomMetadata()->create();

        // Assert
        $this->assertNotNull($report->metadata);
    }

    public function test_with_long_reason_state_sets_long_text(): void
    {
        // Act
        $report = Report::factory()->withLongReason()->create();

        // Assert
        $this->assertGreaterThan(100, strlen((string) $report->reason));
    }

    public function test_with_short_reason_state_sets_short_text(): void
    {
        // Act
        $report = Report::factory()->withShortReason()->create();

        // Assert
        $this->assertLessThan(200, strlen((string) $report->reason));
    }

    public function test_trashed_state_creates_soft_deleted_report(): void
    {
        // Act
        $report = Report::factory()->trashed()->create();

        // Assert
        $this->assertSoftDeleted('reports', ['id' => $report->id]);
    }

    public function test_complete_state_sets_all_fields(): void
    {
        // Arrange
        $user = TestUser::create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
        ]);

        $post = TestPost::create([
            'user_id' => $user->id,
            'title' => 'Post title',
            'body' => 'Post body',
        ]);

        // Act
        $report = Report::factory()
            ->complete(
                reporter: $user,
                reportable: $post,
                type: ReportType::SPAM,
                status: ReportStatus::PENDING,
                reason: 'Full report reason',
                metadata: ['source' => 'api'],
            )
            ->create();

        // Assert
        $this->assertDatabaseHas('reports', [
            'id' => $report->id,
            'reporter_type' => TestUser::class,
            'reporter_id' => $user->id,
            'reportable_type' => TestPost::class,
            'reportable_id' => $post->id,
            'type' => ReportType::SPAM->value,
            'status' => ReportStatus::PENDING->value,
            'reason' => 'Full report reason',
        ]);
        $this->assertEquals('api', $report->metadata?->source);
    }

    public function test_it_creates_multiple_reports(): void
    {
        // Act
        $reports = Report::factory()
            ->count(5)
            ->sequence(fn ($sequence) => [
                'reporter_id' => $sequence->index + 1,
                'reportable_id' => $sequence->index + 1,
            ])
            ->create();

        // Assert
        $this->assertCount(5, $reports);
        $this->assertDatabaseCount('reports', 5);
    }
}
