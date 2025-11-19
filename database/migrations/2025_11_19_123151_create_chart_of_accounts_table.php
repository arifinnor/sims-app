<?php

use App\Enums\Finance\AccountType;
use App\Enums\Finance\NormalBalance;
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
        Schema::create('chart_of_accounts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('category_id')->nullable();
            $table->string('code')->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->uuid('parent_id')->nullable();
            $table->enum('account_type', AccountType::values())->index();
            $table->enum('normal_balance', NormalBalance::values());
            $table->boolean('is_posting')->default(true)->index();
            $table->boolean('is_cash')->default(false)->index();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index('category_id');
            $table->index('parent_id');
        });

        Schema::table('chart_of_accounts', function (Blueprint $table) {
            $table->foreign('category_id')
                ->references('id')
                ->on('account_categories')
                ->nullOnDelete();

            $table->foreign('parent_id')
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
        Schema::dropIfExists('chart_of_accounts');
    }
};
