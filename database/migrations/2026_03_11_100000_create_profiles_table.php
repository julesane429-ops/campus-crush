<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->integer('age');
            $table->enum('gender', ['homme', 'femme']);
            $table->string('ufr');
            $table->string('promotion')->nullable();
            $table->string('field_of_study')->default('Non précisé');
            $table->string('level');
            $table->text('bio')->nullable();
            $table->text('interests')->nullable();
            $table->string('photo')->nullable();
            $table->string('university')->default('UGB');
            $table->timestamp('last_seen_at')->nullable();
            $table->timestamps();

            $table->index('gender');
            $table->index('ufr');
            $table->index('university');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('profiles');
    }
};
