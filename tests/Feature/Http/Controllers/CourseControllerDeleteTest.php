<?php

use App\Models\Course;
use App\Models\Student;
use App\Models\Teacher;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('delete course', function () {
    $this->deleteJson(route('courses.destroy', 1))->assertNotFound();

    Course::factory()
        ->has(Student::factory()->count(2))
        ->forTeacher()
        ->create();

    $this->deleteJson(route('courses.destroy', 1))->assertNoContent();
    $this->assertDatabaseCount(Student::class, 2);
    $this->assertDatabaseCount(Teacher::class, 1);
    $this->assertDatabaseCount(Course::class, 0);
    $this->assertDatabaseCount('course_student', 0);
});
