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
        Schema::create('complaint_evidence', function (Blueprint $table) {
            $table->id();
            $table->foreignId('complaint_id')->constrained()->cascadeOnDelete();
            $table->string('path');
            $table->string('mime_type', 120);
            $table->string('original_name');
            $table->unsignedBigInteger('size');
            $table->timestamps();
        });

        Schema::create('complaint_activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('complaint_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action', 40)->nullable();
            $table->string('from_status', 20)->nullable();
            $table->string('to_status', 20)->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
            $table->index(['complaint_id', 'created_at']);
        });

        Schema::create('barangay_information', function (Blueprint $table) {
            $table->id();
            $table->string('name')->default('Barangay Poblacion');
            $table->string('municipality')->nullable();
            $table->string('province')->nullable();
            $table->string('address')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->text('office_hours')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('barangay_information');
        Schema::dropIfExists('complaint_activities');
        Schema::dropIfExists('complaint_evidence');
    }
};
