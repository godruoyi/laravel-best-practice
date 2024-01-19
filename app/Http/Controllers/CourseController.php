<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCourseRequest;
use App\Http\Requests\UpdateCourseRequest;
use App\Models\Course;
use Illuminate\Http\Request;

class CourseController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        return Course::when($request->name, fn ($query, $name) => $query->where('name', 'like', "%{$name}%"))
            ->withCount('students')
            ->with('teacher')
            ->paginate($request->per_page ?? 10);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCourseRequest $request)
    {
        $course = tap(Course::create($request->validated()), fn ($course) => $course->students()->sync($request->students));

        return response()->json(compact('course'), 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Course $course)
    {
        return $course->load('teacher:id,name', 'students:id,name');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCourseRequest $request, Course $course)
    {
        $course->update($request->validated());
        $course->students()->sync($request->students);

        return response()->noContent();
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Course $course)
    {
        $course->students()->detach();
        $course->delete();

        return response()->noContent();
    }
}
