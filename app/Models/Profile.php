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
        'badge',
        'boosted_until',
    ];

    protected function casts(): array
    {
        return [
            'age'          => 'integer',
            'last_seen_at' => 'datetime',
            'boosted_until' => 'datetime',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function universityModel()
    {
        return $this->belongsTo(University::class, 'university_id');
    }

    public function getInterestsArrayAttribute(): array
    {
        if (empty($this->interests)) return [];
        return array_map('trim', explode(',', $this->interests));
    }

    public function getPhotoUrlAttribute(): string
    {
        if ($this->photo) {
            // Photos statiques dans public/images/avatars
            if (str_starts_with($this->photo, 'avatars/')) {
                return asset('images/' . $this->photo);
            }
            // Photos sur S3/Supabase Storage
            if (config('filesystems.default') === 's3') {
                return config('filesystems.disks.s3.url') . '/' . $this->photo;
            }
            // Photos en local (dev)
            return asset('storage/' . $this->photo);
        }
        $name = $this->user->name ?? 'CC';
        return 'https://ui-avatars.com/api/?background=1a1145&color=ff5e6c&bold=true&name=' . urlencode(substr($name, 0, 2));
    }

    public function getUniversityNameAttribute(): string
    {
        if ($this->universityModel) {
            return $this->universityModel->short_name;
        }
        return $this->university ?? 'N/A';
    }

    public function isBoosted(): bool
    {
        return $this->boosted_until !== null && $this->boosted_until->isFuture();
    }
}
