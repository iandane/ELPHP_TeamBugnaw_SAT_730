<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    protected $fillable = [
        'title', 'description', 'image', 'money_goal', 'deadline', 'user_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
