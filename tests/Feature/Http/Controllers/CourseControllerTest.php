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
        ->assertJsonPath('course.id', 1);

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

it('can load empty course list', function () {
    $this->getJson(route('courses.index'))
        ->assertStatus(200)
        ->assertJsonPath('data', [])
        ->assertJsonPath('total', 0)
        ->assertJsonPath('per_page', 10);
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
        ->assertJsonIsObject('data.0.teacher')
        ->assertJsonPath('total', 1)
        ->assertJsonPath('per_page', 10);
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
        ->assertJsonPath('total', 2)
        ->assertJsonCount(2, 'data')
        ->assertJsonPath('per_page', 3);

    $this->getJson(route('courses.index', ['per_page' => 15, 'name' => 'Lar']))
        ->assertJsonPath('total', 1)
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('per_page', 15);

    $this->getJson(route('courses.index', ['per_page' => 3, 'name' => 'LL']))
        ->assertJsonPath('total', 0)
        ->assertJsonCount(0, 'data')
        ->assertJsonPath('per_page', 3);
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
                ['id', 'name'],
            ],
            'teacher' => [
                'id', 'name',
            ],
        ]);
});

it('update course success', function () {
    Course::factory()
        ->has(Student::factory()->count(2))
        ->forTeacher()
        ->create();

    $this->putJson(route('courses.update', 1))
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['name', 'students']);

    $this->assertDatabaseCount(Student::class, 2);
    Student::factory()->count(2)->create(); // renew 2 students

    $course = [
        'name' => 'Laravel',
        'description' => 'The Best Laravel Course',
        'teacher_id' => 999, // cannot update teacher
        'students' => [1, 3],
    ];

    $this->putJson(route('courses.update', 1), $course)
        ->assertNoContent();

    $course = Course::first();

    expect($course)
        ->students->toHaveCount(2)
        ->students->each->id->toBeIn([1, 3])
        ->name->toBe('Laravel')
        ->teacher->id->toBe(1);
});

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
