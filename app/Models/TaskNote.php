<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;

class TaskNote extends Model
{
    /** @use HasFactory<\Database\Factories\TaskNoteFactory> */
    use HasFactory;

    protected $fillable = [
        'date',
        'task_id',
        'user_id',
        'note',
        'status',
    ];

    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
