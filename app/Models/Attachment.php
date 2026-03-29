<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Attachment extends Model
{
    protected $fillable = [
        'message_id',
        'file_path',
        'file_type',
    ];

    public function message()
    {
        return $this->belongsTo(Message::class);
    }

    public function getUrlAttribute(): string
    {
        if (config('filesystems.default') === 's3') {
            return config('filesystems.disks.s3.url') . '/' . $this->file_path;
        }
        return asset('storage/' . $this->file_path);
    }
}