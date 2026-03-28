<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Ajouter le rôle admin aux utilisateurs
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('is_admin')->default(false)->after('email');
            $table->boolean('is_banned')->default(false)->after('is_admin');
            $table->text('ban_reason')->nullable()->after('is_banned');
        });

        // Table des abonnements
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->enum('status', ['trial', 'active', 'expired', 'cancelled'])->default('trial');
            $table->integer('amount')->default(1000); // en FCFA
            $table->string('payment_method')->nullable(); // orange_money, wave, free_money, etc.
            $table->string('transaction_id')->nullable();
            $table->timestamp('starts_at');
            $table->timestamp('ends_at');
            $table->timestamp('trial_ends_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
        });

        // Table des paiements (historique)
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('subscription_id')->nullable()->constrained()->onDelete('set null');
            $table->integer('amount'); // FCFA
            $table->string('payment_method'); // orange_money, wave, free_money
            $table->string('transaction_id')->nullable();
            $table->string('phone_number')->nullable();
            $table->enum('status', ['pending', 'completed', 'failed', 'refunded'])->default('pending');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
        Schema::dropIfExists('subscriptions');

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['is_admin', 'is_banned', 'ban_reason']);
        });
    }
};
