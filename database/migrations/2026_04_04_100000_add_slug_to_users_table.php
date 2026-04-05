<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('slug')->nullable()->unique()->after('name');
        });

        // Générer un slug pour les utilisateurs existants
        foreach (\App\Models\User::all() as $user) {
            $base = Str::slug($user->name);
            $slug = $base;
            $i = 1;
            while (\App\Models\User::where('slug', $slug)->where('id', '!=', $user->id)->exists()) {
                $slug = $base . '-' . $i++;
            }
            $user->update(['slug' => $slug]);
        }
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('slug');
        });
    }
};
