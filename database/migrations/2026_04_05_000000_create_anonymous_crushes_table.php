<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('anonymous_crushes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sender_id')->constrained('users')->onDelete('cascade');
            $table->string('target_identifier'); // email ou téléphone
            $table->enum('target_type', ['email', 'phone'])->default('email');
            $table->foreignId('target_user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('message', 200)->nullable(); // message perso optionnel
            $table->string('sender_university')->nullable(); // "Quelqu'un de l'UGB..."
            $table->boolean('is_revealed')->default(false);
            $table->timestamp('revealed_at')->nullable();
            $table->timestamps();

            $table->index(['target_identifier']);
            $table->index(['target_user_id']);
            $table->index(['sender_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('anonymous_crushes');
    }
};
