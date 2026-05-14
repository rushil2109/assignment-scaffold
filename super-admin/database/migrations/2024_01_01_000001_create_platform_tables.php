<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('members', function (Blueprint $table) {
            $table->char('id', 36)->primary();
            $table->string('user_id')->unique();
            $table->char('admin_id', 36)->unique();
            $table->string('email')->nullable();
            $table->string('mobile')->nullable();
            $table->string('preferred_name')->nullable();
            $table->json('residential_address')->nullable();
            $table->json('postal_address')->nullable();
            $table->timestamps();
        });

        Schema::create('accounts', function (Blueprint $table) {
            $table->char('id', 36)->primary();
            $table->char('member_id', 36);
            $table->char('account_id', 36)->unique();
            $table->timestamps();

            $table->foreign('member_id')->references('id')->on('members')->onDelete('cascade');
        });

        Schema::create('investment_profiles', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->char('account_id', 36);
            $table->string('asset_code');
            $table->decimal('percentage', 5, 2);
            $table->boolean('is_current')->default(true);
            $table->date('effective_from');
            $table->timestamp('created_at')->nullable();

            $table->foreign('account_id')->references('id')->on('accounts')->onDelete('cascade');
            $table->index(['account_id', 'is_current']);
        });

        Schema::create('transactions', function (Blueprint $table) {
            $table->char('id', 36)->primary();
            $table->char('account_id', 36);
            $table->string('type');
            $table->decimal('amount', 14, 2);
            $table->date('effective_date');
            $table->timestamp('created_at')->nullable();

            $table->foreign('account_id')->references('id')->on('accounts')->onDelete('cascade');
            $table->index(['account_id', 'effective_date']);
        });

        Schema::create('unit_prices', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('asset_code');
            $table->date('date');
            $table->decimal('price', 10, 6);
            $table->timestamp('created_at')->nullable();

            $table->unique(['asset_code', 'date']);
        });

        Schema::create('holdings', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->char('account_id', 36);
            $table->string('asset_code');
            $table->decimal('units', 16, 6);
            $table->decimal('unit_price', 10, 6);
            $table->decimal('balance', 14, 2);
            $table->date('effective_date');
            $table->timestamp('created_at')->nullable();

            $table->foreign('account_id')->references('id')->on('accounts')->onDelete('cascade');
            $table->unique(['account_id', 'asset_code', 'effective_date']);
        });

        Schema::create('audit_operations', function (Blueprint $table) {
            $table->char('id', 36)->primary();
            $table->string('user_id');
            $table->string('operation');
            $table->string('status');
            $table->timestamp('created_at')->nullable();

            $table->index('user_id');
        });

        Schema::create('audit_events', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->char('operation_id', 36);
            $table->string('type');
            $table->json('details')->nullable();
            $table->timestamp('created_at')->nullable();

            $table->foreign('operation_id')->references('id')->on('audit_operations');
            $table->index('operation_id');
        });

        Schema::create('system_state', function (Blueprint $table) {
            $table->integer('id')->primary()->default(1);
            $table->date('current_date');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_events');
        Schema::dropIfExists('audit_operations');
        Schema::dropIfExists('holdings');
        Schema::dropIfExists('unit_prices');
        Schema::dropIfExists('transactions');
        Schema::dropIfExists('investment_profiles');
        Schema::dropIfExists('accounts');
        Schema::dropIfExists('members');
        Schema::dropIfExists('system_state');
    }
};
