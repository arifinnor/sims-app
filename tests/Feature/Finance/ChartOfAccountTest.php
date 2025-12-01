<?php

use App\Enums\Finance\AccountType;
use App\Enums\Finance\NormalBalance;
use App\Enums\Finance\ReportType;
use App\Models\Finance\AccountCategory;
use App\Models\Finance\ChartOfAccount;
use App\Services\Finance\ChartOfAccountService;

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

describe('ChartOfAccountService', function () {
    it('suggests first root code when no accounts exist', function () {
        $service = new ChartOfAccountService;

        $code = $service->suggestNextCode(null);

        expect($code)->toBe('1-0000');
    });

    it('suggests next root code based on existing root codes', function () {
        $category = AccountCategory::factory()->create();

        ChartOfAccount::factory()->create([
            'category_id' => $category->getKey(),
            'code' => '1-0000',
            'name' => 'Assets',
            'parent_id' => null,
        ]);

        ChartOfAccount::factory()->create([
            'category_id' => $category->getKey(),
            'code' => '6-0000',
            'name' => 'Expenses',
            'parent_id' => null,
        ]);

        $service = new ChartOfAccountService;

        $code = $service->suggestNextCode(null);

        expect($code)->toBe('7-0000');
    });

    it('suggests first child code for parent with no children', function () {
        $category = AccountCategory::factory()->create();

        $parent = ChartOfAccount::factory()->create([
            'category_id' => $category->getKey(),
            'code' => '1-1000',
            'name' => 'Current Assets',
            'parent_id' => null,
        ]);

        $service = new ChartOfAccountService;

        $code = $service->suggestNextCode($parent->getKey());

        expect($code)->toBe('1-1100');
    });

    it('suggests next sibling code when parent has existing children', function () {
        $category = AccountCategory::factory()->create();

        $parent = ChartOfAccount::factory()->create([
            'category_id' => $category->getKey(),
            'code' => '1-1000',
            'name' => 'Current Assets',
            'parent_id' => null,
        ]);

        ChartOfAccount::factory()->create([
            'category_id' => $category->getKey(),
            'code' => '1-1101',
            'name' => 'Cash',
            'parent_id' => $parent->getKey(),
        ]);

        ChartOfAccount::factory()->create([
            'category_id' => $category->getKey(),
            'code' => '1-1105',
            'name' => 'Inventory',
            'parent_id' => $parent->getKey(),
        ]);

        $service = new ChartOfAccountService;

        $code = $service->suggestNextCode($parent->getKey());

        expect($code)->toBe('1-1106');
    });

    it('suggests code for first child of root account', function () {
        $category = AccountCategory::factory()->create();

        $root = ChartOfAccount::factory()->create([
            'category_id' => $category->getKey(),
            'code' => '1-0000',
            'name' => 'Assets',
            'parent_id' => null,
        ]);

        $service = new ChartOfAccountService;

        $code = $service->suggestNextCode($root->getKey());

        expect($code)->toBe('1-1000');
    });

    it('handles incremental code suggestion correctly', function () {
        $category = AccountCategory::factory()->create();

        $parent = ChartOfAccount::factory()->create([
            'category_id' => $category->getKey(),
            'code' => '1-1100',
            'name' => 'Cash Accounts',
            'parent_id' => null,
        ]);

        ChartOfAccount::factory()->create([
            'category_id' => $category->getKey(),
            'code' => '1-1101',
            'name' => 'Petty Cash',
            'parent_id' => $parent->getKey(),
        ]);

        $service = new ChartOfAccountService;

        $code = $service->suggestNextCode($parent->getKey());

        expect($code)->toBe('1-1102');
    });
});

describe('ChartOfAccountController::getNextCode', function () {
    beforeEach(function () {
        $this->user = \App\Models\User::factory()->create();
    });

    it('returns next code for root account', function () {
        $response = $this->actingAs($this->user)
            ->getJson('/finance/chart-of-accounts/next-code');

        $response->assertSuccessful()
            ->assertJsonStructure(['code'])
            ->assertJson(['code' => '1-0000']);
    });

    it('returns next code for parent account', function () {
        $category = AccountCategory::factory()->create();

        $parent = ChartOfAccount::factory()->create([
            'category_id' => $category->getKey(),
            'code' => '1-1000',
            'name' => 'Current Assets',
            'parent_id' => null,
        ]);

        $response = $this->actingAs($this->user)
            ->getJson("/finance/chart-of-accounts/next-code?parent_id={$parent->getKey()}");

        $response->assertSuccessful()
            ->assertJsonStructure(['code'])
            ->assertJson(['code' => '1-1100']);
    });

    it('returns next sibling code when parent has children', function () {
        $category = AccountCategory::factory()->create();

        $parent = ChartOfAccount::factory()->create([
            'category_id' => $category->getKey(),
            'code' => '1-1000',
            'name' => 'Current Assets',
            'parent_id' => null,
        ]);

        ChartOfAccount::factory()->create([
            'category_id' => $category->getKey(),
            'code' => '1-1101',
            'name' => 'Cash',
            'parent_id' => $parent->getKey(),
        ]);

        $response = $this->actingAs($this->user)
            ->getJson("/finance/chart-of-accounts/next-code?parent_id={$parent->getKey()}");

        $response->assertSuccessful()
            ->assertJsonStructure(['code'])
            ->assertJson(['code' => '1-1102']);
    });

    it('validates parent_id as uuid when provided', function () {
        $response = $this->actingAs($this->user)
            ->getJson('/finance/chart-of-accounts/next-code?parent_id=invalid-uuid');

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['parent_id']);
    });

    it('returns 422 when parent does not exist', function () {
        $nonExistentId = \Illuminate\Support\Str::uuid()->toString();

        $response = $this->actingAs($this->user)
            ->getJson("/finance/chart-of-accounts/next-code?parent_id={$nonExistentId}");

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['parent_id']);
    });

    it('requires authentication', function () {
        $response = $this->getJson('/finance/chart-of-accounts/next-code');

        $response->assertStatus(401);
    });
});
