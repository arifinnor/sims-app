<?php

use App\Enums\Finance\AccountType;
use App\Enums\Finance\NormalBalance;
use App\Enums\Finance\ReportType;
use App\Models\Finance\AccountCategory;
use App\Models\Finance\ChartOfAccount;

it('casts account category attributes and sequences', function () {
    $category = AccountCategory::factory()->create([
        'name' => 'Test Category',
        'report_type' => ReportType::BalanceSheet->value,
        'sequence' => 10,
    ]);

    expect($category->report_type)->toBeInstanceOf(ReportType::class)
        ->and($category->report_type)->toBe(ReportType::BalanceSheet)
        ->and($category->sequence)->toBe(10);
});

it('manages hierarchical chart of accounts with scopes', function () {
    $category = AccountCategory::factory()->create();

    $header = ChartOfAccount::factory()
        ->header()
        ->asset()
        ->create([
            'category_id' => $category->getKey(),
            'code' => '1-0000',
            'name' => 'Assets',
        ]);

    $posting = ChartOfAccount::factory()
        ->asset()
        ->forParent($header)
        ->create([
            'category_id' => $category->getKey(),
            'code' => '1-1000',
            'name' => 'Cash',
        ]);

    $cash = ChartOfAccount::factory()
        ->asset()
        ->cash()
        ->create([
            'category_id' => $category->getKey(),
            'code' => '1-1100',
            'name' => 'Petty Cash',
        ]);

    expect($posting->parent)->toBeInstanceOf(ChartOfAccount::class)
        ->and($posting->parent->is($header))->toBeTrue()
        ->and($header->children)->toHaveCount(1)
        ->and($posting->account_type)->toBe(AccountType::Asset)
        ->and($posting->normal_balance)->toBe(NormalBalance::Debit);

    expect(ChartOfAccount::posting()->get()->contains(fn ($account) => $account->is($posting)))->toBeTrue()
        ->and(ChartOfAccount::header()->get()->contains(fn ($account) => $account->is($header)))->toBeTrue()
        ->and(ChartOfAccount::cash()->get()->contains(fn ($account) => $account->is($cash)))->toBeTrue()
        ->and(ChartOfAccount::active()->count())->toBe(3);
});
