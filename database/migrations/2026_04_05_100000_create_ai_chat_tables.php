<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_chat_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->enum('bot_type', ['support', 'match_girl', 'match_boy', 'coach', 'flirt'])->default('support');
            $table->string('bot_name', 50);
            $table->string('bot_avatar', 10)->default('🤖');
            $table->boolean('is_active')->default(true);
            $table->integer('message_count')->default(0);
            $table->timestamps();
            $table->index(['user_id', 'bot_type']);
        });

        Schema::create('ai_chat_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('session_id')->constrained('ai_chat_sessions')->onDelete('cascade');
            $table->enum('role', ['user', 'assistant']);
            $table->text('content');
            $table->timestamps();
            $table->index(['session_id', 'created_at']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->boolean('ai_chat_unlocked')->default(false)->after('streak_days');
            $table->timestamp('ai_chat_unlocked_at')->nullable()->after('ai_chat_unlocked');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_chat_messages');
        Schema::dropIfExists('ai_chat_sessions');
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['ai_chat_unlocked', 'ai_chat_unlocked_at']);
        });
    }
};
