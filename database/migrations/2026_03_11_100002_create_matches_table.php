<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('matches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user1_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('user2_id')->constrained('users')->onDelete('cascade');
            $table->boolean('blocked_by_user1')->default(false);
            $table->boolean('blocked_by_user2')->default(false);
            $table->timestamps();

            $table->unique(['user1_id', 'user2_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('matches');
    }
};
