<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;

class CourseCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @return array<int|string, mixed>
     */
    public function toArray(Request $request)
    {
        return $this->collection->map(fn ($course) => [
            'id' => $course->id,
            'name' => $course->name,
            'description' => $course->description,
            'teacher' => new TeacherResource($course->teacher),
            'students_count' => $course->students_count,
            'created_at' => $course->created_at->toDateTimeString(),
            'updated_at' => $course->updated_at->toDateTimeString(),
        ]);
    }
}
