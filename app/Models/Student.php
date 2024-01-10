<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Student extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'name',
        'age',
        'email',
        'phone',
    ];

    /**
     * The courses that belong to the student.
     */
    public function courses(): BelongsToMany
    {
        return $this->belongsToMany(Course::class);
    }
}
