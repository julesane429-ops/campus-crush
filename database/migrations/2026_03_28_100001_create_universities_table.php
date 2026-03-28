<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Créer la table des universités
        Schema::create('universities', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('short_name')->unique(); // ex: UGB, UCAD, UADB
            $table->string('city');
            $table->string('region')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Ajouter la FK sur profiles (remplace la colonne string 'university')
        Schema::table('profiles', function (Blueprint $table) {
            $table->foreignId('university_id')->nullable()->after('university')->constrained('universities')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('profiles', function (Blueprint $table) {
            $table->dropForeign(['university_id']);
            $table->dropColumn('university_id');
        });
        Schema::dropIfExists('universities');
    }
};
