<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // --------------------------------------------
        // 1) Clean up any legacy table / FKs / triggers
        //    (handles both `education_background` and `education_background`)
        // --------------------------------------------
        Schema::disableForeignKeyConstraints();

        // Drop triggers if they exist (only one set per schema)
        try {
            DB::unprepared("DROP TRIGGER IF EXISTS trg_eb_year_not_future_bi");
        } catch (\Throwable $e) {}

        try {
            DB::unprepared("DROP TRIGGER IF EXISTS trg_eb_year_not_future_bu");
        } catch (\Throwable $e) {}

        // Best-effort FK cleanup on both possible table names
        foreach (['education_background', 'education_background'] as $tbl) {
            try {
                DB::statement("ALTER TABLE `$tbl` DROP FOREIGN KEY `fk_eb_company`");
            } catch (\Throwable $e) {}
            try {
                DB::statement("ALTER TABLE `$tbl` DROP FOREIGN KEY `fk_eb_registration`");
            } catch (\Throwable $e) {}
            try {
                DB::statement("ALTER TABLE `$tbl` DROP FOREIGN KEY `fk_eb_degree`");
            } catch (\Throwable $e) {}
        }

        // Drop both case variants of the table (no error if missing)
        Schema::dropIfExists('education_background');
        Schema::dropIfExists('education_background');

        Schema::enableForeignKeyConstraints();

        // --------------------------------------------
        // 2) Re-create table with clean schema + proper FKs
        // --------------------------------------------
        Schema::create('education_background', function (Blueprint $table) {
            $table->id();

            // Foreign keys
            $table->unsignedBigInteger('Company_id');      // companies.id
            $table->unsignedBigInteger('registration_id'); // registration_master.id
            $table->unsignedBigInteger('degree_id');       // degrees.id

            // Main fields
            $table->string('Institution', 180);
            $table->year('Passing_Year');

            // Common result types used in BD contexts
            $table->enum('Result_Type', ['GPA','CGPA','Division','Class','Percentage']);
            $table->string('obtained_grade_or_score', 20);
            $table->unsignedInteger('Out_of');

            // Meta
            $table->tinyInteger('status')->default(1); // 1=Active, 0=Inactive
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();

            // Uniqueness: one record per (Company, Registration, Degree)
            $table->unique(['Company_id','registration_id','degree_id'], 'uq_eb_company_reg_degree');

            // Helpful indexes
            $table->index(['Company_id','registration_id'], 'idx_eb_company_reg');
            $table->index('degree_id', 'idx_eb_degree');
            $table->index('Passing_Year', 'idx_eb_year');
            $table->index('Result_Type', 'idx_eb_result_type');
            $table->index('status', 'idx_eb_status');

            // FKs
            $table->foreign('Company_id', 'fk_eb_company')
                ->references('id')->on('companies')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();

            $table->foreign('registration_id', 'fk_eb_registration')
                ->references('id')->on('registration_master')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();

            $table->foreign('degree_id', 'fk_eb_degree')
                ->references('id')->on('degrees')
                ->restrictOnDelete()   // prevent deleting a degree that’s in use
                ->cascadeOnUpdate();
        });

        // --------------------------------------------
        // 3) Recreate triggers (no DELIMITER needed)
        // --------------------------------------------
        DB::unprepared("
            CREATE TRIGGER trg_eb_year_not_future_bi
            BEFORE INSERT ON education_background
            FOR EACH ROW
            BEGIN
                IF NEW.Passing_Year > YEAR(CURDATE()) THEN
                    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Passing_Year cannot be in the future';
                END IF;
            END
        ");

        DB::unprepared("
            CREATE TRIGGER trg_eb_year_not_future_bu
            BEFORE UPDATE ON education_background
            FOR EACH ROW
            BEGIN
                IF NEW.Passing_Year > YEAR(CURDATE()) THEN
                    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Passing_Year cannot be in the future';
                END IF;
            END
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop triggers first (if present)
        try {
            DB::unprepared("DROP TRIGGER IF EXISTS trg_eb_year_not_future_bi");
        } catch (\Throwable $e) {}

        try {
            DB::unprepared("DROP TRIGGER IF EXISTS trg_eb_year_not_future_bu");
        } catch (\Throwable $e) {}

        // Drop table (both variants, to be safe)
        Schema::dropIfExists('education_background');
        Schema::dropIfExists('education_background');
    }
};
