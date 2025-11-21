<?php

use App\Enums\Finance\JournalStatus;
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
        Schema::create('journal_entries', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('transaction_type_id')->index();
            $table->date('transaction_date')->index();
            $table->string('reference_number')->unique()->index();
            $table->text('description')->nullable();
            $table->enum('status', JournalStatus::values())->default(JournalStatus::Posted->value);
            $table->decimal('total_amount', 19, 4);
            $table->uuid('student_id')->nullable()->index();
            $table->foreignId('created_by')->nullable();
            $table->timestamps();
        });

        Schema::table('journal_entries', function (Blueprint $table) {
            $table->foreign('transaction_type_id')
                ->references('id')
                ->on('transaction_types')
                ->restrictOnDelete();

            $table->foreign('student_id')
                ->references('id')
                ->on('students')
                ->nullOnDelete();

            $table->foreign('created_by')
                ->references('id')
                ->on('users')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('journal_entries');
    }
};
