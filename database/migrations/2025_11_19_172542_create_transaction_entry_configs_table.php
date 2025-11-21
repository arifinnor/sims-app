<?php

use App\Enums\Finance\AccountType;
use App\Enums\Finance\EntryPosition;
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
        Schema::create('transaction_accounts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('transaction_type_id')->index();
            $table->string('role');
            $table->string('label');
            $table->enum('direction', EntryPosition::values());
            $table->enum('account_type', AccountType::values());
            $table->uuid('chart_of_account_id')->nullable()->index();
            $table->timestamps();

            $table->unique(['transaction_type_id', 'role']);
        });

        Schema::table('transaction_accounts', function (Blueprint $table) {
            $table->foreign('transaction_type_id')
                ->references('id')
                ->on('transaction_types')
                ->cascadeOnDelete();

            $table->foreign('chart_of_account_id')
                ->references('id')
                ->on('chart_of_accounts')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transaction_accounts');
    }
};
