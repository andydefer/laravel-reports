<?php

declare(strict_types=1);

namespace AndyDefer\LaravelReports\Tests\Integration\Services;

use AndyDefer\LaravelReports\Enums\ReportStatus;
use AndyDefer\LaravelReports\Enums\ReportType;
use AndyDefer\LaravelReports\Models\Report;
use AndyDefer\LaravelReports\Repositories\ReportRepository;
use AndyDefer\LaravelReports\Services\ReportService;
use AndyDefer\LaravelReports\Tests\Fixtures\Models\TestPost;
use AndyDefer\LaravelReports\Tests\Fixtures\Models\TestUser;
use AndyDefer\LaravelReports\Tests\IntegrationTestCase;
use AndyDefer\PhpVo\ValueObjects\DateTimeVO;
use AndyDefer\Repository\Configs\RepositoryConfig;
use AndyDefer\Repository\Contracts\Configs\RepositoryConfigInterface;
use Illuminate\Support\Collection;
use RuntimeException;

final class ReportServiceTest extends IntegrationTestCase
{
    private ReportService $reportService;

    private TestUser $user;

    private TestPost $post;

    protected function setUp(): void
    {
        parent::setUp();

        // ✅ Configurer les enum casts pour le repository
        $this->app['config']->set('repository.enum_casts', [
            'reports' => [
                'type' => ReportType::class,
                'status' => ReportStatus::class,
            ],
        ]);

        // ✅ Rebinder RepositoryConfig
        $this->app->singleton(RepositoryConfig::class, function ($app) {
            return new RepositoryConfig($app['config']);
        });

        $this->app->bind(RepositoryConfigInterface::class, RepositoryConfig::class);

        $this->reportService = new ReportService(
            new ReportRepository
        );

        $this->user = TestUser::create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
        ]);

        $this->post = TestPost::create([
            'user_id' => $this->user->id,
            'title' => 'Test Post',
            'body' => 'Test content',
        ]);
    }

    public function test_report_creates_report(): void
    {
        $report = $this->reportService->report(
            $this->user,
            $this->post,
            ReportType::SPAM,
            ReportStatus::PENDING,
            'Contenu promotionnel'
        );

        $this->assertInstanceOf(Report::class, $report);
        $this->assertDatabaseHas('reports', [
            'id' => $report->id,
            'reporter_type' => TestUser::class,
            'reporter_id' => $this->user->id,
            'reportable_type' => TestPost::class,
            'reportable_id' => $this->post->id,
            'type' => 'spam',
            'reason' => 'Contenu promotionnel',
            'status' => 'pending',
        ]);
    }

    public function test_report_throws_exception_when_already_reported(): void
    {
        $this->reportService->report(
            $this->user,
            $this->post,
            ReportType::SPAM,
            ReportStatus::PENDING,
            'Contenu promotionnel'
        );

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Vous avez déjà signalé ce contenu.');

        $this->reportService->report(
            $this->user,
            $this->post,
            ReportType::HARASSMENT,
            ReportStatus::PENDING,
            'Contenu inapproprié'
        );
    }

    public function test_has_reported_returns_true_when_reported(): void
    {
        $this->reportService->report(
            $this->user,
            $this->post,
            ReportType::SPAM,
            ReportStatus::PENDING,
            'Contenu promotionnel'
        );

        $result = $this->reportService->hasReported($this->user, $this->post);

        $this->assertTrue($result);
    }

    public function test_has_reported_returns_false_when_not_reported(): void
    {
        $result = $this->reportService->hasReported($this->user, $this->post);

        $this->assertFalse($result);
    }

    public function test_get_reports_for_returns_reports(): void
    {
        $this->reportService->report(
            $this->user,
            $this->post,
            ReportType::SPAM,
            ReportStatus::PENDING,
            'Contenu promotionnel'
        );

        $reports = $this->reportService->getReportsFor($this->post);

        $this->assertInstanceOf(Collection::class, $reports);
        $this->assertCount(1, $reports);
        $this->assertEquals(ReportType::SPAM, $reports->first()->type);
        $this->assertEquals(ReportStatus::PENDING, $reports->first()->status);
    }

    public function test_get_reports_for_with_only_pending(): void
    {
        $this->reportService->report(
            $this->user,
            $this->post,
            ReportType::SPAM,
            ReportStatus::PENDING,
            'Contenu promotionnel'
        );

        $reports = $this->reportService->getReportsFor($this->post, true);

        $this->assertCount(1, $reports);
    }

    public function test_get_reports_by_returns_reports_from_user(): void
    {
        $this->reportService->report(
            $this->user,
            $this->post,
            ReportType::SPAM,
            ReportStatus::PENDING,
            'Contenu promotionnel'
        );

        $reports = $this->reportService->getReportsBy($this->user);

        $this->assertInstanceOf(Collection::class, $reports);
        $this->assertCount(1, $reports);
    }

    public function test_get_pending_reports_returns_pending_reports(): void
    {
        $this->reportService->report(
            $this->user,
            $this->post,
            ReportType::SPAM,
            ReportStatus::PENDING,
            'Contenu promotionnel'
        );

        $pending = $this->reportService->getPendingReports();

        $this->assertCount(1, $pending);
        $this->assertEquals(ReportStatus::PENDING, $pending->first()->status);
    }

    public function test_get_reports_by_status_returns_filtered_reports(): void
    {
        $report = $this->reportService->report(
            $this->user,
            $this->post,
            ReportType::SPAM,
            ReportStatus::PENDING,
            'Contenu promotionnel'
        );

        $this->reportService->updateStatus($report->id, ReportStatus::REVIEWED);

        $reviewed = $this->reportService->getReportsByStatus(ReportStatus::REVIEWED);

        $this->assertCount(1, $reviewed);
        $this->assertEquals(ReportStatus::REVIEWED, $reviewed->first()->status);
    }

    public function test_get_reports_by_type_returns_filtered_reports(): void
    {
        $this->reportService->report(
            $this->user,
            $this->post,
            ReportType::SPAM,
            ReportStatus::PENDING,
            'Contenu promotionnel'
        );

        $spam = $this->reportService->getReportsByType(ReportType::SPAM);

        $this->assertCount(1, $spam);
        $this->assertEquals(ReportType::SPAM, $spam->first()->type);
    }

    public function test_find_returns_report_by_id(): void
    {
        $report = $this->reportService->report(
            $this->user,
            $this->post,
            ReportType::SPAM,
            ReportStatus::PENDING,
            'Contenu promotionnel'
        );

        $found = $this->reportService->find($report->id);

        $this->assertNotNull($found);
        $this->assertEquals($report->id, $found->id);
    }

    public function test_find_returns_null_when_not_found(): void
    {
        $found = $this->reportService->find(999);

        $this->assertNull($found);
    }

    public function test_update_status_updates_report_status(): void
    {
        $report = $this->reportService->report(
            $this->user,
            $this->post,
            ReportType::SPAM,
            ReportStatus::PENDING,
            'Contenu promotionnel'
        );

        $updated = $this->reportService->updateStatus($report->id, ReportStatus::RESOLVED);

        $this->assertEquals(ReportStatus::RESOLVED, $updated->status);
        $this->assertDatabaseHas('reports', [
            'id' => $report->id,
            'status' => 'resolved',
        ]);
    }

    public function test_update_status_throws_exception_when_not_found(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Report 999 not found');

        $this->reportService->updateStatus(999, ReportStatus::RESOLVED);
    }

    public function test_update_type_updates_report_type(): void
    {
        $report = $this->reportService->report(
            $this->user,
            $this->post,
            ReportType::SPAM,
            ReportStatus::PENDING,
            'Contenu promotionnel'
        );

        $updated = $this->reportService->updateType($report->id, ReportType::HARASSMENT);

        $this->assertEquals(ReportType::HARASSMENT, $updated->type);
        $this->assertDatabaseHas('reports', [
            'id' => $report->id,
            'type' => 'harassment',
        ]);
    }

    public function test_update_type_throws_exception_when_not_found(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Report 999 not found');

        $this->reportService->updateType(999, ReportType::HARASSMENT);
    }

    public function test_count_reports_returns_correct_count(): void
    {
        $user2 = TestUser::create([
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
        ]);

        $this->reportService->report(
            $this->user,
            $this->post,
            ReportType::SPAM,
            ReportStatus::PENDING,
            'Contenu promotionnel'
        );

        $this->reportService->report(
            $user2,
            $this->post,
            ReportType::HARASSMENT,
            ReportStatus::PENDING,
            'Contenu offensant'
        );

        $count = $this->reportService->countReports($this->post);

        $this->assertEquals(2, $count);
    }

    public function test_count_reports_with_only_pending(): void
    {
        $report = $this->reportService->report(
            $this->user,
            $this->post,
            ReportType::SPAM,
            ReportStatus::PENDING,
            'Contenu promotionnel'
        );

        $this->reportService->report(
            TestUser::create(['name' => 'Jane Doe', 'email' => 'jane@example.com']),
            $this->post,
            ReportType::HARASSMENT,
            ReportStatus::PENDING,
            'Contenu offensant'
        );

        $this->reportService->updateStatus($report->id, ReportStatus::REVIEWED);

        $pendingCount = $this->reportService->countReports($this->post, true);

        $this->assertEquals(1, $pendingCount);
    }

    public function test_count_by_status_returns_correct_count(): void
    {
        $report1 = $this->reportService->report(
            $this->user,
            $this->post,
            ReportType::SPAM,
            ReportStatus::PENDING,
            'Contenu promotionnel'
        );

        $report2 = $this->reportService->report(
            TestUser::create(['name' => 'Jane Doe', 'email' => 'jane@example.com']),
            $this->post,
            ReportType::HARASSMENT,
            ReportStatus::PENDING,
            'Contenu offensant'
        );

        $this->reportService->updateStatus($report1->id, ReportStatus::RESOLVED);

        $resolvedCount = $this->reportService->countByStatus(ReportStatus::RESOLVED);

        $this->assertEquals(1, $resolvedCount);
    }

    public function test_count_by_type_returns_correct_count(): void
    {
        $this->reportService->report(
            $this->user,
            $this->post,
            ReportType::SPAM,
            ReportStatus::PENDING,
            'Contenu promotionnel'
        );

        $this->reportService->report(
            TestUser::create(['name' => 'Jane Doe', 'email' => 'jane@example.com']),
            $this->post,
            ReportType::HARASSMENT,
            ReportStatus::PENDING,
            'Contenu offensant'
        );

        $spamCount = $this->reportService->countByType(ReportType::SPAM);

        $this->assertEquals(1, $spamCount);
    }

    public function test_delete_removes_report(): void
    {
        $report = $this->reportService->report(
            $this->user,
            $this->post,
            ReportType::SPAM,
            ReportStatus::PENDING,
            'Contenu promotionnel'
        );

        $this->reportService->delete($report->id);

        $this->assertSoftDeleted('reports', [
            'id' => $report->id,
        ]);
    }

    public function test_delete_throws_exception_when_not_found(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Report 999 not found');

        $this->reportService->delete(999);
    }

    public function test_get_reports_updated_after_returns_filtered_reports(): void
    {
        $report = $this->reportService->report(
            $this->user,
            $this->post,
            ReportType::SPAM,
            ReportStatus::PENDING,
            'Contenu promotionnel'
        );

        $pastDate = DateTimeVO::from(now()->subDay()->toIso8601String());

        $reports = $this->reportService->getReportsUpdatedAfter($pastDate);

        $this->assertCount(1, $reports);
    }
}
