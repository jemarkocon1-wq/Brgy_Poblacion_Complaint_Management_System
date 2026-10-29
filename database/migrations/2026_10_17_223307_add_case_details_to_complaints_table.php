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
            $table->text('respondents')->nullable()->after('title');
            $table->string('case_subject')->nullable()->after('respondents');
            $table->date('incident_date')->nullable()->after('case_subject');
            $table->text('relief_sought')->nullable()->after('description');
            $table->text('witnesses')->nullable()->after('relief_sought');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('complaints', function (Blueprint $table) {
            $table->dropColumn([
                'respondents',
                'case_subject',
                'incident_date',
                'relief_sought',
                'witnesses',
            ]);
        });
    }
};
