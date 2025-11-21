<?php

use App\Enums\Finance\EntryDirection;
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
        Schema::create('journal_entry_lines', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('journal_entry_id')->index();
            $table->uuid('chart_of_account_id')->index();
            $table->enum('direction', EntryDirection::values());
            $table->decimal('amount', 19, 4);
            $table->string('description')->nullable();
            $table->timestamps();

            $table->index(['chart_of_account_id', 'direction']);
        });

        Schema::table('journal_entry_lines', function (Blueprint $table) {
            $table->foreign('journal_entry_id')
                ->references('id')
                ->on('journal_entries')
                ->cascadeOnDelete();

            $table->foreign('chart_of_account_id')
                ->references('id')
                ->on('chart_of_accounts')
                ->restrictOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('journal_entry_lines');
    }
};
