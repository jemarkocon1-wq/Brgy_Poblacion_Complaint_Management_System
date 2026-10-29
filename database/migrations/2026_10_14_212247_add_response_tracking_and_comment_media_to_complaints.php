<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('complaints', function (Blueprint $table) {
            $table->timestamp('approved_at')->nullable()->after('status');
            $table->timestamp('staff_responded_at')->nullable()->after('approved_at');
            $table->timestamp('closed_at')->nullable()->after('resolved_at');
            $table->index(['status', 'approved_at'], 'complaints_status_approved_at_index');
        });

        Schema::table('complaint_evidence', function (Blueprint $table) {
            $table->foreignId('complaint_comment_id')
                ->nullable()
                ->after('complaint_id')
                ->constrained('complaint_comments')
                ->cascadeOnDelete();
            $table->index('complaint_comment_id', 'complaint_evidence_comment_id_index');
        });

        Schema::table('complaint_comments', function (Blueprint $table) {
            $table->text('body')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('complaint_comments')->whereNull('body')->update(['body' => '']);

        Schema::table('complaint_comments', function (Blueprint $table) {
            $table->text('body')->nullable(false)->change();
        });

        Schema::table('complaint_evidence', function (Blueprint $table) {
            $table->dropForeign(['complaint_comment_id']);
            $table->dropIndex('complaint_evidence_comment_id_index');
            $table->dropColumn('complaint_comment_id');
        });

        Schema::table('complaints', function (Blueprint $table) {
            $table->dropIndex('complaints_status_approved_at_index');
            $table->dropColumn(['approved_at', 'staff_responded_at', 'closed_at']);
        });
    }
};
