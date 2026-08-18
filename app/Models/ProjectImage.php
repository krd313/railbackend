<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjectImage extends Model
{
    /** @use HasFactory<\Database\Factories\ProjectImageFactory> */
    use HasFactory;

    protected $fillable = [
        'project_id',
        'user_id',
        'image_path',
        'file_name',
        'file_type',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
