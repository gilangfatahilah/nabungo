<?php

use App\Models\Account;
use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use App\Services\TransactionService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);

    $this->account = Account::factory()->create([
        'user_id' => $this->user->id,
        'balance' => 1000,
        'type' => 'cash',
    ]);

    $this->category = Category::factory()->create([
        'user_id' => $this->user->id,
        'type' => 'expense',
    ]);

    $this->service = app(TransactionService::class);
});

test('user can view transaction index page', function () {
    $response = $this->get(route('transaction.index'));

    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page->component('transaction/Index'));
});

test('user can create income transaction', function () {
    $transactionData = [
        'account_id' => $this->account->id,
        'category_id' => $this->category->id,
        'type' => 'income',
        'amount' => 500,
        'description' => 'Salary payment',
        'transaction_date' => now()->format('Y-m-d'),
    ];

    $response = $this->post(route('transaction.store'), $transactionData);

    $response->assertRedirect(route('transaction.index'));
    $response->assertSessionHas('success');

    $this->assertDatabaseHas('transactions', [
        'user_id' => $this->user->id,
        'account_id' => $this->account->id,
        'type' => 'income',
        'amount' => 500,
    ]);

    // Check balance increased
    $this->account->refresh();
    expect($this->account->balance)->toEqual(1500);
});

test('user can create expense transaction', function () {
    $transactionData = [
        'account_id' => $this->account->id,
        'category_id' => $this->category->id,
        'type' => 'expense',
        'amount' => 200,
        'description' => 'Groceries',
        'transaction_date' => now()->format('Y-m-d'),
    ];

    $response = $this->post(route('transaction.store'), $transactionData);

    $response->assertRedirect(route('transaction.index'));

    // Check balance decreased
    $this->account->refresh();
    expect($this->account->balance)->toEqual(800);
});

test('user can create transfer transaction', function () {
    $targetAccount = Account::factory()->create([
        'user_id' => $this->user->id,
        'balance' => 500,
        'type' => 'bank',
    ]);

    $transactionData = [
        'account_id' => $this->account->id,
        'account_target_id' => $targetAccount->id,
        'type' => 'transfer',
        'amount' => 300,
        'description' => 'Transfer to bank',
        'transaction_date' => now()->format('Y-m-d'),
    ];

    $response = $this->post(route('transaction.store'), $transactionData);

    $response->assertRedirect(route('transaction.index'));

    // Check source account decreased
    $this->account->refresh();
    expect($this->account->balance)->toEqual(700);

    // Check target account increased
    $targetAccount->refresh();
    expect($targetAccount->balance)->toEqual(800);
});

test('expense transaction fails when insufficient balance', function () {
    $transactionData = [
        'account_id' => $this->account->id,
        'category_id' => $this->category->id,
        'type' => 'expense',
        'amount' => 1500, // More than current balance
        'description' => 'Expensive item',
        'transaction_date' => now()->format('Y-m-d'),
    ];

    $response = $this->post(route('transaction.store'), $transactionData);

    $response->assertSessionHasErrors();

    // Balance should remain unchanged
    $this->account->refresh();
    expect($this->account->balance)->toEqual(1000);
});

test('transfer fails when source and target accounts are the same', function () {
    $transactionData = [
        'account_id' => $this->account->id,
        'account_target_id' => $this->account->id, // Same account
        'type' => 'transfer',
        'amount' => 100,
        'description' => 'Invalid transfer',
        'transaction_date' => now()->format('Y-m-d'),
    ];

    $response = $this->post(route('transaction.store'), $transactionData);

    $response->assertSessionHasErrors();

    // Balance should remain unchanged
    $this->account->refresh();
    expect($this->account->balance)->toEqual(1000);
});

test('user can update transaction', function () {
    $transaction = $this->service->create([
        'user_id' => $this->user->id,
        'account_id' => $this->account->id,
        'category_id' => $this->category->id,
        'type' => 'income',
        'amount' => 100,
        'transaction_date' => now()->format('Y-m-d'),
    ]);

    // Account balance should be 1100 after transaction
    $this->account->refresh();
    expect($this->account->balance)->toEqual(1100);

    $updateData = [
        'account_id' => $this->account->id,
        'category_id' => $this->category->id,
        'type' => 'income',
        'amount' => 200, // Changed amount
        'description' => 'Updated income',
        'transaction_date' => now()->format('Y-m-d'),
    ];

    $response = $this->put(route('transaction.update', $transaction), $updateData);

    $response->assertRedirect(route('transaction.index'));

    // Check balance recalculated correctly (1000 + 200)
    $this->account->refresh();
    expect($this->account->balance)->toEqual(1200);
});

test('user can delete transaction', function () {
    $transaction = $this->service->create([
        'user_id' => $this->user->id,
        'account_id' => $this->account->id,
        'category_id' => $this->category->id,
        'type' => 'expense',
        'amount' => 100,
        'transaction_date' => now()->format('Y-m-d'),
    ]);

    // Account balance should be 900 after transaction
    $this->account->refresh();
    expect($this->account->balance)->toEqual(900);

    $response = $this->delete(route('transaction.destroy', $transaction));

    $response->assertRedirect(route('transaction.index'));

    $this->assertDatabaseMissing('transactions', [
        'id' => $transaction->id,
    ]);

    // Check balance restored
    $this->account->refresh();
    expect($this->account->balance)->toEqual(1000);
});

test('user can delete multiple transactions', function () {
    $transaction1 = $this->service->create([
        'user_id' => $this->user->id,
        'account_id' => $this->account->id,
        'category_id' => $this->category->id,
        'type' => 'income',
        'amount' => 100,
        'transaction_date' => now()->format('Y-m-d'),
    ]);

    $transaction2 = $this->service->create([
        'user_id' => $this->user->id,
        'account_id' => $this->account->id,
        'category_id' => $this->category->id,
        'type' => 'expense',
        'amount' => 50,
        'transaction_date' => now()->format('Y-m-d'),
    ]);

    // Account balance should be 1050 after transactions
    $this->account->refresh();
    expect($this->account->balance)->toEqual(1050);

    $response = $this->delete(route('transaction.multiple-destroy'), [
        'ids' => [$transaction1->id, $transaction2->id],
    ]);

    $response->assertRedirect(route('transaction.index'));

    $this->assertDatabaseMissing('transactions', ['id' => $transaction1->id]);
    $this->assertDatabaseMissing('transactions', ['id' => $transaction2->id]);

    // Check balance restored
    $this->account->refresh();
    expect($this->account->balance)->toEqual(1000);
});

test('user cannot update another users transaction', function () {
    $this->actingAs($this->user);

    $otherUser = User::factory()->create();

    $otherAccount = Account::factory()->create([
        'user_id' => $otherUser->id,
        'balance' => 1000,
    ]);

    $otherCategory = Category::factory()->create([
        'user_id' => $otherUser->id,
        'type' => 'income',
    ]);

    $transaction = Transaction::factory()->create([
        'user_id' => $otherUser->id,
        'account_id' => $otherAccount->id,
        'category_id' => $otherCategory->id,
        'type' => 'income',
        'amount' => 100,
    ]);

    $originalAmount = $transaction->amount;

    $response = $this->put(
        route('transaction.update', $transaction),
        [
            'account_id' => $otherAccount->id,
            'category_id' => $otherCategory->id,
            'type' => 'income',
            'amount' => 200,
            'transaction_date' => now()->format('Y-m-d'),
        ]
    );

    $response->assertForbidden();

    $transaction->refresh();

    expect($transaction->amount)->toEqual($originalAmount);
});

test('user cannot delete another users transaction', function () {
    $this->actingAs($this->user);

    $otherUser = User::factory()->create();

    $otherAccount = Account::factory()->create([
        'user_id' => $otherUser->id,
        'balance' => 1000,
    ]);

    $otherCategory = Category::factory()->create([
        'user_id' => $otherUser->id,
        'type' => 'income',
    ]);

    $transaction = Transaction::factory()->create([
        'user_id' => $otherUser->id,
        'account_id' => $otherAccount->id,
        'category_id' => $otherCategory->id,
        'type' => 'income',
        'amount' => 100,
    ]);

    $response = $this->delete(route('transaction.destroy', $transaction));

    $response->assertForbidden();

    $this->assertDatabaseHas('transactions', [
        'id' => $transaction->id,
    ]);
});

test('account history is created for income transaction', function () {
    $transactionData = [
        'account_id' => $this->account->id,
        'category_id' => $this->category->id,
        'type' => 'income',
        'amount' => 500,
        'description' => 'Test income',
        'transaction_date' => now()->format('Y-m-d'),
    ];

    $this->post(route('transaction.store'), $transactionData);

    $this->assertDatabaseHas('account_histories', [
        'user_id' => $this->user->id,
        'account_id' => $this->account->id,
        'type' => 'in',
        'amount' => 500,
        'balance_before' => 1000,
        'balance_after' => 1500,
    ]);
});

test('account history is created for expense transaction', function () {
    $transactionData = [
        'account_id' => $this->account->id,
        'category_id' => $this->category->id,
        'type' => 'expense',
        'amount' => 300,
        'description' => 'Test expense',
        'transaction_date' => now()->format('Y-m-d'),
    ];

    $this->post(route('transaction.store'), $transactionData);

    $this->assertDatabaseHas('account_histories', [
        'user_id' => $this->user->id,
        'account_id' => $this->account->id,
        'type' => 'out',
        'amount' => 300,
        'balance_before' => 1000,
        'balance_after' => 700,
    ]);
});

test('account history is created for both accounts in transfer', function () {
    $targetAccount = Account::factory()->create([
        'user_id' => $this->user->id,
        'balance' => 500,
        'type' => 'bank',
    ]);

    $transactionData = [
        'account_id' => $this->account->id,
        'account_target_id' => $targetAccount->id,
        'type' => 'transfer',
        'amount' => 200,
        'description' => 'Transfer test',
        'transaction_date' => now()->format('Y-m-d'),
    ];

    $this->post(route('transaction.store'), $transactionData);

    // Check source account history
    $this->assertDatabaseHas('account_histories', [
        'user_id' => $this->user->id,
        'account_id' => $this->account->id,
        'type' => 'out',
        'amount' => 200,
        'balance_before' => 1000,
        'balance_after' => 800,
    ]);

    // Check target account history
    $this->assertDatabaseHas('account_histories', [
        'user_id' => $this->user->id,
        'account_id' => $targetAccount->id,
        'type' => 'in',
        'amount' => 200,
        'balance_before' => 500,
        'balance_after' => 700,
    ]);
});

test('transaction requires authentication', function () {
    auth()->logout();

    $response = $this->get(route('transaction.index'));

    $response->assertRedirect(route('login'));
});

test('creating transaction with invalid data fails validation', function () {
    $transactionData = [
        'account_id' => 99999, // Non-existent account
        'type' => 'income',
        'amount' => -100, // Negative amount
        'transaction_date' => 'invalid-date',
    ];

    $response = $this->post(route('transaction.store'), $transactionData);

    $response->assertSessionHasErrors(['account_id', 'amount', 'transaction_date']);
});
