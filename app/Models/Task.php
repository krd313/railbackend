<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    /** @use HasFactory<\Database\Factories\TaskFactory> */
    use HasFactory;

    protected $appends = ['notes'];

    protected $fillable = [
        'project_id',
        'user_id',
        'reference',
        'name',
        'description',
        'status',
        'priority',
        'image',
        'due_date',
    ];

    // Automatically append the title attribute to JSON output
    // protected $appends = ['title'];

    // // Accessor for backward compatibility - provides 'title' field that returns 'name'
    // public function getTitleAttribute(): ?string
    // {
    //     return $this->name;
    // }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function images()
    {
        return $this->hasMany(TaskImage::class);
    }

    public function taskNotes()
    {
        return $this->hasMany(TaskNote::class);
    }

    public function getNotesAttribute(): ?string
    {
        if ($this->relationLoaded('taskNotes')) {
            return optional($this->taskNotes->sortByDesc('id')->first())->note;
        }

        return $this->taskNotes()->latest('id')->value('note');
    }
}
