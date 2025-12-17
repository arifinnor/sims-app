<?php

use App\Enums\Academic\EnrollmentStatus;
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
        Schema::create('class_students', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('classroom_id');
            $table->uuid('student_id');
            $table->enum('status', EnrollmentStatus::values())->default(EnrollmentStatus::Active->value)->index();
            $table->timestamps();

            $table->unique(['classroom_id', 'student_id']);
            $table->index('classroom_id');
            $table->index('student_id');
        });

        Schema::table('class_students', function (Blueprint $table) {
            $table->foreign('classroom_id')
                ->references('id')
                ->on('classrooms')
                ->restrictOnDelete();

            $table->foreign('student_id')
                ->references('id')
                ->on('students')
                ->restrictOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('class_students');
    }
};
