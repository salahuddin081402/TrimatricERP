<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('training_category', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';

            $table->unsignedBigInteger('Company_id');
            $table->unsignedBigInteger('training_category_Id');
            $table->string('training_category_Name', 150);

            $table->tinyInteger('status')->default(1);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();

            $table->primary(['Company_id', 'training_category_Id']);
            $table->unique(['Company_id', 'training_category_Name'], 'uq_company_category_name');
            $table->index(['status'], 'idx_tc_status');

            $table->foreign('Company_id', 'fk_tc_company')
                  ->references('id')->on('companies')
                  ->onDelete('cascade');
        });
    }

    public function down(): void {
        Schema::dropIfExists('training_category');
    }
};
