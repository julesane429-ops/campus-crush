<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use App\Models\User;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Colonne referral_code sur users
        Schema::table('users', function (Blueprint $table) {
            $table->string('referral_code', 10)->nullable()->unique()->after('is_banned');
            $table->unsignedBigInteger('referred_by')->nullable()->after('referral_code');
            $table->foreign('referred_by')->references('id')->on('users')->nullOnDelete();
        });

        // 2. Table des parrainages
        Schema::create('referrals', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('referrer_id'); // le parrain
            $table->unsignedBigInteger('referred_id'); // le filleul
            $table->boolean('rewarded')->default(false); // récompense accordée ?
            $table->timestamp('rewarded_at')->nullable();
            $table->timestamps();

            $table->foreign('referrer_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('referred_id')->references('id')->on('users')->cascadeOnDelete();
            $table->unique('referred_id'); // un filleul ne peut avoir qu'un parrain
        });

        // 3. Générer un code pour les utilisateurs existants
        User::whereNull('referral_code')->each(function ($user) {
            $user->update(['referral_code' => strtoupper(substr(md5($user->id . $user->email), 0, 8))]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('referrals');
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['referred_by']);
            $table->dropColumn(['referral_code', 'referred_by']);
        });
    }
};
