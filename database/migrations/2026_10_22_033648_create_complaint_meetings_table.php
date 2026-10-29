<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('complaint_meetings', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('complaint_id')->constrained()->cascadeOnDelete();
            $table->foreignId('recorded_by_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('meeting_type', 20);
            $table->date('meeting_date');
            $table->text('participants')->nullable();
            $table->text('minutes');
            $table->string('outcome', 32);
            $table->text('settlement_terms')->nullable();
            $table->timestamps();
            $table->index(['complaint_id', 'meeting_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('complaint_meetings');
    }
};
