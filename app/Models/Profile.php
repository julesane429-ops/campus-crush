<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Profile extends Model
{
    protected $fillable = [
        'user_id',
        'age',
        'gender',
        'ufr',
        'promotion',
        'field_of_study',
        'level',
        'bio',
        'interests',
        'photo',
        'university',
        'university_id',
        'last_seen_at',
    ];

    protected function casts(): array
    {
        return [
            'age' => 'integer',
            'last_seen_at' => 'datetime',
        ];
    }

    // ── Relations ──

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function universityModel()
    {
        return $this->belongsTo(University::class, 'university_id');
    }

    // ── Helpers ──

    public function getInterestsArrayAttribute(): array
    {
        if (empty($this->interests)) {
            return [];
        }
        return array_map('trim', explode(',', $this->interests));
    }

    public function getPhotoUrlAttribute(): string
    {
        if ($this->photo) {
            return asset('storage/' . $this->photo);
        }
        return asset('storage/profiles/default-avatar.png');
    }

    /**
     * Nom de l'université (depuis la relation ou le champ texte legacy).
     */
    public function getUniversityNameAttribute(): string
    {
        if ($this->universityModel) {
            return $this->universityModel->short_name;
        }
        return $this->university ?? 'N/A';
    }
}
