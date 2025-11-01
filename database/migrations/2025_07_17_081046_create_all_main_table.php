<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void
  {
    Schema::create('accounts', function (Blueprint $table) {
      $table->id();
      $table->foreignId('user_id')->constrained()->cascadeOnDelete();
      $table->string('name');
      $table->enum('type', ['cash', 'bank', 'ewallet', 'asset', 'goal',  'liability']);
      $table->decimal('balance', 20, 2)->default(0);
      $table->text('notes')->nullable();
      $table->timestamps();

      // Indexes
      $table->index(['user_id', 'type']);
    });

    Schema::create('categories', function (Blueprint $table) {
      $table->id();
      $table->foreignId('user_id')->constrained()->cascadeOnDelete();
      $table->string('name');
      $table->enum('type', ['income', 'expense']);
      $table->timestamps();

      // Indexes
      $table->index(['user_id', 'type']);
    });

    Schema::create('transactions', function (Blueprint $table) {
      $table->id();
      $table->foreignId('user_id')->constrained()->cascadeOnDelete();
      $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
      $table->foreignId('account_id')->constrained('accounts')->cascadeOnDelete();
      $table->foreignId('account_target_id')->nullable()->constrained('accounts')->nullOnDelete();
      $table->enum('type', ['income', 'expense', 'transfer']);
      $table->decimal('amount', 20, 2);
      $table->text('description')->nullable();
      $table->date('transaction_date');
      $table->string('proof_file')->nullable();
      $table->timestamps();

      // Indexes
      $table->index(['user_id', 'category_id', 'account_id', 'account_target_id', 'type', 'transaction_date']);
    });

    Schema::create('account_histories', function (Blueprint $table) {
      $table->id();
      $table->foreignId('user_id')->constrained()->cascadeOnDelete();
      $table->foreignId('account_id')->constrained('accounts')->cascadeOnDelete();
      $table->foreignId('transaction_id')->nullable()->constrained('transactions')->nullOnDelete();
      $table->enum('type', ['in', 'out']);
      $table->decimal('amount', 20, 2);
      $table->decimal('balance_before', 20, 2);
      $table->decimal('balance_after', 20, 2);
      $table->text('notes')->nullable();
      $table->timestamps();

      // Indexes
      $table->index(['user_id', 'account_id', 'transaction_id', 'type']);
    });

    Schema::create('budgets', function (Blueprint $table) {
      $table->id();
      $table->foreignId('user_id')->constrained()->cascadeOnDelete();
      $table->foreignId('category_id')->constrained()->cascadeOnDelete();
      $table->timestamp('month');
      $table->decimal('amount', 20, 2);
      $table->timestamps();

      // Indexes
      $table->index(['user_id', 'category_id', 'month']);
    });

    Schema::create('debts', function (Blueprint $table) {
      $table->id();
      $table->foreignId('user_id')->constrained()->cascadeOnDelete();
      $table->string('title');
      $table->enum('type', ['debt', 'receivable']);
      $table->decimal('amount', 20, 2);
      $table->decimal('paid_amount', 20, 2)->default(0);
      $table->string('contact_name')->nullable();
      $table->string('contact_phone')->nullable();
      $table->date('due_date')->nullable();
      $table->text('notes')->nullable();
      $table->enum('status', ['unpaid', 'paid'])->default('unpaid');
      $table->timestamps();

      // Indexes
      $table->index(['user_id', 'type', 'status']);
    });

    Schema::create('debt_payments', function (Blueprint $table) {
      $table->id();
      $table->foreignId('user_id')->constrained()->cascadeOnDelete();
      $table->foreignId('debt_id')->constrained('debts')->cascadeOnDelete();
      $table->foreignId('transaction_id')->nullable()->constrained('transactions')->nullOnDelete();
      $table->decimal('amount', 20, 2);
      $table->date('payment_date');
      $table->text('notes')->nullable();
      $table->timestamps();

      // Indexes
      $table->index(['user_id', 'debt_id', 'transaction_id', 'payment_date']);
    });

    Schema::create('goals', function (Blueprint $table) {
      $table->id();
      $table->foreignId('account_id')->constrained();
      $table->foreignId('user_id')->constrained()->cascadeOnDelete();
      $table->string('title');
      $table->decimal('target_amount', 20, 2);
      $table->decimal('saved_amount', 20, 2)->default(0);
      $table->date('due_date')->nullable();
      $table->text('notes')->nullable();
      $table->enum('status', ['ongoing', 'achieved', 'cancelled'])->default('ongoing');
      $table->timestamps();

      // Indexes
      $table->index(['user_id', 'account_id', 'status']);
    });
  }

  public function down(): void
  {
    Schema::dropIfExists('debt_payments');
    Schema::dropIfExists('debts');
    Schema::dropIfExists('budgets');
    Schema::dropIfExists('account_histories');
    Schema::dropIfExists('transactions');
    Schema::dropIfExists('categories');
    Schema::dropIfExists('accounts');
    Schema::dropIfExists('goals');
  }
};
