<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    use HasFactory;

    protected $fillable = [
        'content',
        'user_id',
        'project_id',
    ];

    // Define the relationship with the User model
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Define the relationship with the Project model
    public function project()
    {
        return $this->belongsTo(Project::class);
    }
}

