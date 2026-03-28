<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pass extends Model
{
    protected $fillable = [
        'user_id',
        'passed_user_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function passedUser()
    {
        return $this->belongsTo(User::class, 'passed_user_id');
    }
}
