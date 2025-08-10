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
        Schema::create('payments', function (Blueprint $table) {
        $table->id();
        $table->foreignId('reservation_id')->constrained();
        $table->foreignId('payer_id')->constrained('users');
        $table->foreignId('payee_id')->constrained('users');
        $table->decimal('amount', 10, 2);
        $table->string('payment_method')->nullable();
        $table->string('status')->default('pending');
        $table->string('tx_ref')->nullable()->unique();
        $table->string('first_name')->nullable();
        $table->string('last_name')->nullable();
        $table->string('email')->nullable();
        $table->string('phone')->nullable();
        $table->timestamps();
    });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
