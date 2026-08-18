<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaskImage extends Model
{
    /** @use HasFactory<\Database\Factories\TaskImageFactory> */
    use HasFactory;

    protected $fillable = [
        'task_id',
        'user_id',
        'image_path',
        'file_name',
        'file_type',
    ];

    public function task()
    {
        return $this->belongsTo(Task::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
