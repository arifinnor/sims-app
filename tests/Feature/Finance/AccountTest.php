<?php

use App\Models\Finance\Account;

test('account can be created', function () {
    $account = Account::factory()->create([
        'account_number' => '1000',
        'name' => 'Cash',
        'type' => 'asset',
        'category' => 'current_asset',
        'balance' => 10000.50,
        'currency' => 'IDR',
        'status' => 'active',
    ]);

    expect($account)->toBeInstanceOf(Account::class);
    expect($account->account_number)->toBe('1000');
    expect($account->name)->toBe('Cash');
    expect($account->type)->toBe('asset');
    expect($account->category)->toBe('current_asset');
    expect((float) $account->balance)->toBe(10000.50);
    expect($account->currency)->toBe('IDR');
    expect($account->status)->toBe('active');

    $this->assertDatabaseHas('accounts', [
        'account_number' => '1000',
        'name' => 'Cash',
        'type' => 'asset',
    ]);
});

test('account has default currency of IDR', function () {
    $account = Account::factory()->create([
        'account_number' => '2000',
        'name' => 'Accounts Payable',
    ]);

    expect($account->currency)->toBe('IDR');
});

test('account has default status of active', function () {
    $account = Account::factory()->create([
        'account_number' => '3000',
        'name' => 'Equity',
    ]);

    expect($account->status)->toBe('active');
});

test('account has default balance of zero', function () {
    $account = Account::factory()->create([
        'account_number' => '4000',
        'name' => 'Revenue',
        'balance' => 0,
    ]);

    expect((float) $account->balance)->toBe(0.0);
});

test('account number must be unique', function () {
    Account::factory()->create([
        'account_number' => '5000',
        'name' => 'First Account',
    ]);

    expect(fn () => Account::factory()->create([
        'account_number' => '5000',
        'name' => 'Duplicate Account',
    ]))->toThrow(\Illuminate\Database\QueryException::class);
});

test('account can have parent account', function () {
    $parent = Account::factory()->create([
        'account_number' => '1000',
        'name' => 'Assets',
        'type' => 'asset',
    ]);

    $child = Account::factory()->create([
        'account_number' => '1010',
        'name' => 'Current Assets',
        'type' => 'asset',
        'parent_account_id' => $parent->id,
    ]);

    expect($child->parent)->toBeInstanceOf(Account::class);
    expect($child->parent->id)->toBe($parent->id);
    expect($parent->children)->toHaveCount(1);
    expect($parent->children->first()->id)->toBe($child->id);
});

test('account can have multiple children', function () {
    $parent = Account::factory()->create([
        'account_number' => '1000',
        'name' => 'Assets',
        'type' => 'asset',
    ]);

    $child1 = Account::factory()->create([
        'account_number' => '1010',
        'name' => 'Current Assets',
        'type' => 'asset',
        'parent_account_id' => $parent->id,
    ]);

    $child2 = Account::factory()->create([
        'account_number' => '1020',
        'name' => 'Fixed Assets',
        'type' => 'asset',
        'parent_account_id' => $parent->id,
    ]);

    expect($parent->children)->toHaveCount(2);
    expect($parent->children->pluck('id')->toArray())->toContain($child1->id, $child2->id);
});

test('account can get all descendants recursively', function () {
    $root = Account::factory()->create([
        'account_number' => '1000',
        'name' => 'Assets',
        'type' => 'asset',
    ]);

    $level1 = Account::factory()->create([
        'account_number' => '1010',
        'name' => 'Current Assets',
        'type' => 'asset',
        'parent_account_id' => $root->id,
    ]);

    $level2a = Account::factory()->create([
        'account_number' => '1011',
        'name' => 'Cash',
        'type' => 'asset',
        'parent_account_id' => $level1->id,
    ]);

    $level2b = Account::factory()->create([
        'account_number' => '1012',
        'name' => 'Bank',
        'type' => 'asset',
        'parent_account_id' => $level1->id,
    ]);

    $descendants = $root->descendants();

    expect($descendants)->toHaveCount(3);
    expect($descendants->pluck('id')->toArray())->toContain($level1->id, $level2a->id, $level2b->id);
});

test('account can get all ancestors up to root', function () {
    $root = Account::factory()->create([
        'account_number' => '1000',
        'name' => 'Assets',
        'type' => 'asset',
    ]);

    $level1 = Account::factory()->create([
        'account_number' => '1010',
        'name' => 'Current Assets',
        'type' => 'asset',
        'parent_account_id' => $root->id,
    ]);

    $level2 = Account::factory()->create([
        'account_number' => '1011',
        'name' => 'Cash',
        'type' => 'asset',
        'parent_account_id' => $level1->id,
    ]);

    $ancestors = $level2->ancestors();

    expect($ancestors)->toHaveCount(2);
    expect($ancestors->first()->id)->toBe($root->id);
    expect($ancestors->last()->id)->toBe($level1->id);
});

test('account without parent returns empty ancestors collection', function () {
    $account = Account::factory()->create([
        'account_number' => '1000',
        'name' => 'Assets',
        'type' => 'asset',
    ]);

    expect($account->ancestors())->toBeEmpty();
});

test('isAsset returns true for asset accounts', function () {
    $account = Account::factory()->asset()->create([
        'account_number' => '1000',
        'name' => 'Cash',
    ]);

    expect($account->isAsset())->toBeTrue();
    expect($account->isLiability())->toBeFalse();
    expect($account->isEquity())->toBeFalse();
    expect($account->isRevenue())->toBeFalse();
    expect($account->isExpense())->toBeFalse();
});

test('isLiability returns true for liability accounts', function () {
    $account = Account::factory()->liability()->create([
        'account_number' => '2000',
        'name' => 'Accounts Payable',
    ]);

    expect($account->isLiability())->toBeTrue();
    expect($account->isAsset())->toBeFalse();
});

test('isEquity returns true for equity accounts', function () {
    $account = Account::factory()->equity()->create([
        'account_number' => '3000',
        'name' => 'Capital',
    ]);

    expect($account->isEquity())->toBeTrue();
    expect($account->isAsset())->toBeFalse();
});

test('isRevenue returns true for revenue accounts', function () {
    $account = Account::factory()->revenue()->create([
        'account_number' => '4000',
        'name' => 'Tuition Revenue',
    ]);

    expect($account->isRevenue())->toBeTrue();
    expect($account->isAsset())->toBeFalse();
});

test('isExpense returns true for expense accounts', function () {
    $account = Account::factory()->expense()->create([
        'account_number' => '5000',
        'name' => 'Salaries',
    ]);

    expect($account->isExpense())->toBeTrue();
    expect($account->isAsset())->toBeFalse();
});

test('isParent returns true when account has children', function () {
    $parent = Account::factory()->create([
        'account_number' => '1000',
        'name' => 'Assets',
        'type' => 'asset',
    ]);

    Account::factory()->create([
        'account_number' => '1010',
        'name' => 'Current Assets',
        'type' => 'asset',
        'parent_account_id' => $parent->id,
    ]);

    expect($parent->isParent())->toBeTrue();
    expect($parent->isLeaf())->toBeFalse();
});

test('isLeaf returns true when account has no children', function () {
    $account = Account::factory()->create([
        'account_number' => '1011',
        'name' => 'Cash',
        'type' => 'asset',
    ]);

    expect($account->isLeaf())->toBeTrue();
    expect($account->isParent())->toBeFalse();
});

test('getFullAccountNumber returns hierarchical account number', function () {
    $root = Account::factory()->create([
        'account_number' => '1000',
        'name' => 'Assets',
        'type' => 'asset',
    ]);

    $level1 = Account::factory()->create([
        'account_number' => '1010',
        'name' => 'Current Assets',
        'type' => 'asset',
        'parent_account_id' => $root->id,
    ]);

    $level2 = Account::factory()->create([
        'account_number' => '1011',
        'name' => 'Cash',
        'type' => 'asset',
        'parent_account_id' => $level1->id,
    ]);

    expect($level2->getFullAccountNumber())->toBe('1000.1010.1011');
    expect($level1->getFullAccountNumber())->toBe('1000.1010');
    expect($root->getFullAccountNumber())->toBe('1000');
});

test('account can be soft deleted', function () {
    $account = Account::factory()->create([
        'account_number' => '1000',
        'name' => 'Cash',
        'type' => 'asset',
    ]);

    $accountId = $account->id;
    $account->delete();

    expect(Account::find($accountId))->toBeNull();
    expect(Account::withTrashed()->find($accountId))->not->toBeNull();
    expect(Account::withTrashed()->find($accountId)->trashed())->toBeTrue();
});

test('soft deleted account can be restored', function () {
    $account = Account::factory()->create([
        'account_number' => '1000',
        'name' => 'Cash',
        'type' => 'asset',
    ]);

    $accountId = $account->id;
    $account->delete();

    expect(Account::find($accountId))->toBeNull();

    $account->restore();

    expect(Account::find($accountId))->not->toBeNull();
    expect(Account::find($accountId)->trashed())->toBeFalse();
});

test('balance is cast to decimal', function () {
    $account = Account::factory()->create([
        'account_number' => '1000',
        'name' => 'Cash',
        'type' => 'asset',
        'balance' => 12345.67,
    ]);

    expect((float) $account->balance)->toBe(12345.67);
    expect($account->balance)->toBeString();
});
