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
        Schema::table('complaints', function (Blueprint $table) {
            $table->string('receipt_proof_path')->nullable();
            $table->string('receipt_proof_original_name')->nullable();
            $table->string('receipt_proof_mime_type', 100)->nullable();
            $table->unsignedBigInteger('receipt_proof_size')->nullable();
            $table->timestamp('receipt_uploaded_at')->nullable();
            $table->timestamp('receipt_confirmed_at')->nullable();
            $table->string('summons_case_number', 80)->nullable();
            $table->string('summons_for')->nullable();
            $table->date('summons_hearing_date')->nullable();
            $table->string('summons_hearing_time', 5)->nullable();
            $table->date('summons_issued_date')->nullable();
            $table->string('summons_signatory')->nullable();
            $table->timestamp('summons_ready_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('complaints', function (Blueprint $table) {
            $table->dropColumn([
                'receipt_proof_path',
                'receipt_proof_original_name',
                'receipt_proof_mime_type',
                'receipt_proof_size',
                'receipt_uploaded_at',
                'receipt_confirmed_at',
                'summons_case_number',
                'summons_for',
                'summons_hearing_date',
                'summons_hearing_time',
                'summons_issued_date',
                'summons_signatory',
                'summons_ready_at',
            ]);
        });
    }
};
