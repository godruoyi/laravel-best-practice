<?php

use App\Models\Course;
use App\Models\Student;
use App\Models\Teacher;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('create course fails if the teacher is not exist', function () {
    $course = [
        'name' => 'Laravel',
        'description' => 'The Best Laravel Course',
        'teacher_id' => 1, // teacher not exist
    ];

    $this->postJson(route('courses.store'), $course)
        ->assertStatus(422)
        ->assertJsonValidationErrors(['teacher_id']);
});

it('create course fails if the student is not exist', function () {
    Teacher::factory()->create();
    Student::factory()->create();

    $this->assertDatabaseCount(Teacher::class, 1);
    $this->assertDatabaseCount(Student::class, 1);

    $course = [
        'name' => 'Laravel',
        'description' => 'The Best Laravel Course',
        'teacher_id' => 1,
        'students' => [1, 2], // student 2 not exist
    ];

    $this->postJson(route('courses.store'), $course)
        ->assertStatus(422)
        ->assertJsonValidationErrors(['students.1']);
});

it('create course successfully with 0 students', function () {
    Teacher::factory()->create();

    $this->assertDatabaseCount(Teacher::class, 1);

    $course = [
        'name' => 'Laravel',
        'description' => 'The Best Laravel Course',
        'teacher_id' => 1,
    ];

    $this->postJson(route('courses.store'), $course)
        ->assertStatus(201)
        ->assertJsonPath('id', 1);

    // no students
    $this->assertDatabaseCount('course_student', 0);
});

it('create course successfully with 1 students', function () {
    Teacher::factory()->create(['name' => 'Godruoyi']);
    Student::factory()->create(['name' => 'Bob']);

    $this->assertDatabaseCount(Teacher::class, 1);
    $this->assertDatabaseCount(Student::class, 1);

    $course = [
        'name' => 'Laravel',
        'description' => 'The Best Laravel Course',
        'teacher_id' => 1,
        'students' => [1],
    ];

    $this->postJson(route('courses.store'), $course)
        ->assertStatus(201);

    expect(Course::find(1))
        ->students->toHaveCount(1)
        ->students->first()->name->toBe('Bob')
        ->teacher->name->toBe('Godruoyi');
});
