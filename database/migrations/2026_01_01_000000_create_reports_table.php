<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reports', function (Blueprint $table) {
            $table->id();

            // Longueur limitée à 191 pour permettre l'index unique composite en utf8mb4
            $table->string('reporter_type', 191);
            $table->string('reporter_id', 191);
            $table->string('reportable_type', 191);
            $table->string('reportable_id', 191);

            $table->string('type');
            $table->longText('reason');
            $table->json('metadata')->nullable();
            $table->string('status');
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['reporter_type', 'reporter_id'], 'reports_reporter_index');
            $table->index(['reportable_type', 'reportable_id'], 'reports_reportable_index');
            $table->index('type', 'reports_type_index');
            $table->index('status', 'reports_status_index');

            $table->unique(
                ['reporter_type', 'reporter_id', 'reportable_type', 'reportable_id'],
                'reports_unique',
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reports');
    }
};
