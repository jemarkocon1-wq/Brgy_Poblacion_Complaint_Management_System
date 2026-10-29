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
        Schema::create('complaint_summons_services', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('complaint_id')->constrained()->cascadeOnDelete();
            $table->foreignId('recorded_by_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('served_at');
            $table->string('server_name');
            $table->string('recipient_name')->nullable();
            $table->string('result', 24);
            $table->string('signer_type', 20);
            $table->string('signer_name')->nullable();
            $table->text('refusal_response')->nullable();
            $table->string('proof_path');
            $table->string('proof_original_name');
            $table->string('proof_mime_type', 100);
            $table->unsignedBigInteger('proof_size');
            $table->timestamps();
            $table->index(['complaint_id', 'served_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('complaint_summons_services');
    }
};
