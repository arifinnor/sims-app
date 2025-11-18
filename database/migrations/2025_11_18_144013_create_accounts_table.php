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
        Schema::create('accounts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('account_number')->unique();
            $table->string('name');
            $table->enum('type', ['asset', 'liability', 'equity', 'revenue', 'expense']);
            $table->string('category')->nullable();
            $table->uuid('parent_account_id')->nullable();
            $table->decimal('balance', 15, 2)->default(0);
            $table->string('currency')->default('IDR');
            $table->enum('status', ['active', 'inactive', 'archived'])->default('active');
            $table->text('description')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('type');
            $table->index('status');
            $table->index('parent_account_id');
        });

        Schema::table('accounts', function (Blueprint $table) {
            $table->foreign('parent_account_id')->references('id')->on('accounts')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('accounts');
    }
};
