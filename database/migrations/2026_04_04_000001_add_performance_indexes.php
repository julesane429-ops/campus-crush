<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── messages ──────────────────────────────────────────────────────
        Schema::table('messages', function (Blueprint $table) {
            // Comptage des non-lus : WHERE match_id=? AND read_at IS NULL AND sender_id != ?
            $table->index(['match_id', 'read_at'], 'idx_messages_match_unread');

            // Dernier message d'un match : WHERE match_id=? ORDER BY created_at DESC
            // (match_id, created_at) existe déjà → on n'en recrée pas
        });

        // ── matches ───────────────────────────────────────────────────────
        Schema::table('matches', function (Blueprint $table) {
            // forUser() : WHERE user1_id=? OR user2_id=?
            $table->index('user2_id', 'idx_matches_user2');
            // user1_id est déjà indexé via la FK
        });

        // ── likes ─────────────────────────────────────────────────────────
        Schema::table('likes', function (Blueprint $table) {
            // getFavoriteUfr() : WHERE user_id=? + join likedUser.profile
            // liked_user_id est FK donc indexé, mais on ajoute (user_id, liked_user_id)
            // pour couvrir la requête WHERE user_id=? ORDER BY ...
            // Note: unique(['user_id','liked_user_id']) couvre déjà user_id en prefix
            // On ajoute l'index sur liked_user_id seul pour les requêtes inverses
            $table->index('liked_user_id', 'idx_likes_liked_user');
        });

        // ── profiles ──────────────────────────────────────────────────────
        Schema::table('profiles', function (Blueprint $table) {
            // Statut en ligne : WHERE last_seen_at > ?
            $table->index('last_seen_at', 'idx_profiles_last_seen');
            // Filtre age dans le swipe : WHERE age BETWEEN ? AND ?
            $table->index('age', 'idx_profiles_age');
            // Filtre boosted_until : WHERE boosted_until > now()
            $table->index('boosted_until', 'idx_profiles_boosted');
        });

        // ── passes ────────────────────────────────────────────────────────
        // unique(['user_id','passed_user_id']) couvre déjà les requêtes
        // Rien à ajouter
    }

    public function down(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            $table->dropIndex('idx_messages_match_unread');
        });
        Schema::table('matches', function (Blueprint $table) {
            $table->dropIndex('idx_matches_user2');
        });
        Schema::table('likes', function (Blueprint $table) {
            $table->dropIndex('idx_likes_liked_user');
        });
        Schema::table('profiles', function (Blueprint $table) {
            $table->dropIndex('idx_profiles_last_seen');
            $table->dropIndex('idx_profiles_age');
            $table->dropIndex('idx_profiles_boosted');
        });
    }
};