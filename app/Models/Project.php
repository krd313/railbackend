<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    /** @use HasFactory<\Database\Factories\ProjectFactory> */
    use HasFactory;

        protected $fillable = [
        'name',
        'description',
        'notes',
        'due_date',
        'status',
        'priority',
        // 'image',
        'user_id',
        'share_with',
    ];

            public function tasks()
    {
        return $this->hasMany(Task::class);
    }

            public function projectImages()
            {
                return $this->hasMany(ProjectImage::class);
            }
}
