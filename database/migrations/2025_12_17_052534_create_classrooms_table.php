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
        Schema::create('classrooms', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('academic_year_id');
            $table->uuid('homeroom_teacher_id')->nullable();
            $table->integer('grade_level')->index();
            $table->string('name');
            $table->integer('capacity')->default(30);
            $table->timestamps();
            $table->softDeletes();

            $table->index('academic_year_id');
            $table->index('homeroom_teacher_id');
        });

        Schema::table('classrooms', function (Blueprint $table) {
            $table->foreign('academic_year_id')
                ->references('id')
                ->on('academic_years')
                ->restrictOnDelete();

            $table->foreign('homeroom_teacher_id')
                ->references('id')
                ->on('teachers')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('classrooms');
    }
};
