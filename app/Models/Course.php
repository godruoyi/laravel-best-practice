<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Course extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'name',
        'description',
        'content',
    ];

    /**
     * The students that belong to the course.
     */
    public function students(): BelongsToMany
    {
        return $this->belongsToMany(Student::class);
    }

    /**
     * The course's teacher
     */
    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class);
    }
}
