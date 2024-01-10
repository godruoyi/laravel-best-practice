<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\Student;
use App\Models\Teacher;
use Illuminate\Database\Seeder;

class CourseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $students = Student::all();
        $teachers = Teacher::all();

        Course::factory()->count(10)->make()->each(function ($course) use ($students, $teachers) {
            $course->teacher()->associate($teachers->random());
            $course->save();
            $course->students()->attach($students->random(rand(0, 9)));
        });
    }
}
