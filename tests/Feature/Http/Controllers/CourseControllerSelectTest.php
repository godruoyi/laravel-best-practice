<?php

use App\Models\Course;
use App\Models\Student;
use App\Models\Teacher;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('can load empty course list', function () {
    $this->getJson(route('courses.index'))
        ->assertStatus(200)
        ->assertJsonPath('data', [])
        ->assertJsonPath('meta.total', 0)
        ->assertJsonPath('meta.per_page', 10);
});

it('load one courses', function () {
    Course::factory()
        ->has(Student::factory()->count(2))
        ->forTeacher()
        ->create();

    $this->assertDatabaseCount(Course::class, 1);
    $this->assertDatabaseCount(Student::class, 2);
    $this->assertDatabaseCount(Teacher::class, 1);
    $this->assertDatabaseCount('course_student', 2);

    $this->getJson(route('courses.index'))
        ->assertStatus(200)
        ->assertJsonPath('data.0.id', 1)
        ->assertJsonPath('data.0.students_count', 2) // students count
        ->assertJsonMissingPath('data.0.content') // course list not contains content
        ->assertJsonIsObject('data.0.teacher')
        ->assertJsonPath('meta.total', 1)
        ->assertJsonPath('meta.per_page', 10);
});

it('show one course when query by name', function () {
    Course::factory()
        ->has(Student::factory()->count(2))
        ->forTeacher()
        ->sequence(['name' => 'Laravel'], ['name' => 'Python'])
        ->count(2)
        ->create();

    $this->assertDatabaseCount(Course::class, 2);
    $this->assertDatabaseCount(Student::class, 4);
    $this->assertDatabaseCount(Teacher::class, 1);
    $this->assertDatabaseCount('course_student', 4);

    $this->getJson(route('courses.index', ['per_page' => 3]))
        ->assertJsonPath('meta.total', 2)
        ->assertJsonCount(2, 'data')
        ->assertJsonPath('meta.per_page', 3);

    $this->getJson(route('courses.index', ['per_page' => 15, 'name' => 'Lar']))
        ->assertJsonPath('meta.total', 1)
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('meta.per_page', 15);

    $this->getJson(route('courses.index', ['per_page' => 3, 'name' => 'LL']))
        ->assertJsonPath('meta.total', 0)
        ->assertJsonCount(0, 'data')
        ->assertJsonPath('meta.per_page', 3);
});

it('show course detail', function () {
    $this->getJson(route('courses.show', 1))->assertNotFound();

    Course::factory()
        ->has(Student::factory()->count(2))
        ->forTeacher()
        ->create();

    $this->getJson(route('courses.show', 1))
        ->assertOk()
        ->assertJsonPath('id', 1)
        ->assertJsonIsArray('students')
        ->assertJsonIsObject('teacher')
        ->assertJsonCount(2, 'students')
        ->assertJsonStructure([
            'students' => [
                ['id', 'name', 'age', 'email', 'phone'],
            ],
            'teacher' => [
                'id', 'name',
            ],
        ]);
});
