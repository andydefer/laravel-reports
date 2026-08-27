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
            $table->morphs('reporter');
            $table->morphs('reportable');
            $table->string('type');
            $table->mediumText('reason');
            $table->json('metadata')->nullable();
            $table->string('status');
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['reporter_type', 'reporter_id', 'reportable_type', 'reportable_id'], 'reports_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reports');
    }
};
