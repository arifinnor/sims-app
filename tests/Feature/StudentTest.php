<?php

use App\Models\Student;
use App\Models\User;

beforeEach(function () {
    config([
        'app.key' => 'base64:'.base64_encode(random_bytes(32)),
    ]);
});

test('students index page is displayed', function () {
    $actingUser = User::factory()->create();
    Student::factory(3)->create();

    $response = $this
        ->actingAs($actingUser)
        ->get(route('students.index'));

    $response->assertOk();
});

test('student can be created', function () {
    $actingUser = User::factory()->create();

    $response = $this
        ->actingAs($actingUser)
        ->from(route('students.create'))
        ->post(route('students.store'), [
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'phone' => '+1234567890',
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('students.index'));

    $this->assertDatabaseHas('students', [
        'email' => 'jane@example.com',
    ]);

    $student = Student::where('email', 'jane@example.com')->first();
    expect($student->student_number)
        ->toStartWith('STU-')
        ->toHaveLength(9)
        ->toMatch('/^STU-[A-Z0-9]{5}$/');
});

test('student can be created without email', function () {
    $actingUser = User::factory()->create();

    $response = $this
        ->actingAs($actingUser)
        ->from(route('students.create'))
        ->post(route('students.store'), [
            'name' => 'John Doe',
            'email' => null,
            'phone' => null,
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('students.index'));

    $this->assertDatabaseHas('students', [
        'name' => 'John Doe',
        'email' => null,
    ]);
});

test('student creation validates unique email', function () {
    $actingUser = User::factory()->create();
    $existingStudent = Student::factory()->create(['email' => 'jane@example.com']);

    $response = $this
        ->actingAs($actingUser)
        ->from(route('students.create'))
        ->post(route('students.store'), [
            'name' => 'Jane Duplicate',
            'email' => $existingStudent->email,
            'phone' => '+1234567890',
        ]);

    $response
        ->assertSessionHasErrors('email')
        ->assertRedirect(route('students.create'));

    $this->assertDatabaseCount('students', 1);
});

test('student number is auto-generated and unique', function () {
    $actingUser = User::factory()->create();

    $student1 = Student::factory()->create();
    $student2 = Student::factory()->create();
    $student3 = Student::factory()->create();

    expect($student1->student_number)
        ->not->toBe($student2->student_number)
        ->not->toBe($student3->student_number);

    expect($student1->student_number)
        ->toStartWith('STU-')
        ->toHaveLength(9)
        ->toMatch('/^STU-[A-Z0-9]{5}$/');
});

test('student can be updated', function () {
    $actingUser = User::factory()->create();
    $student = Student::factory()->create();

    $response = $this
        ->actingAs($actingUser)
        ->from(route('students.edit', $student))
        ->put(route('students.update', $student), [
            'student_number' => 'STU-A1B2C',
            'name' => 'Updated Name',
            'email' => 'updated@example.com',
            'phone' => '+9876543210',
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('students.edit', $student));

    $student->refresh();

    expect($student->name)->toBe('Updated Name')
        ->and($student->email)->toBe('updated@example.com')
        ->and($student->student_number)->toBe('STU-A1B2C')
        ->and($student->phone)->toBe('+9876543210');
});

test('student can be updated to remove email', function () {
    $actingUser = User::factory()->create();
    $student = Student::factory()->create(['email' => 'old@example.com']);

    $response = $this
        ->actingAs($actingUser)
        ->from(route('students.edit', $student))
        ->put(route('students.update', $student), [
            'student_number' => $student->student_number,
            'name' => $student->name,
            'email' => null,
            'phone' => $student->phone,
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('students.edit', $student));

    $student->refresh();

    expect($student->email)->toBeNull();
});

test('student can be deleted', function () {
    $actingUser = User::factory()->create();
    $student = Student::factory()->create();

    $response = $this
        ->actingAs($actingUser)
        ->from(route('students.index'))
        ->delete(route('students.destroy', $student));

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('students.index'));

    $this->assertSoftDeleted('students', [
        'id' => $student->id,
    ]);
});

test('soft deleted students do not appear in index', function () {
    $actingUser = User::factory()->create();
    $activeStudent = Student::factory()->create();
    $deletedStudent = Student::factory()->create();
    $deletedStudent->delete();

    $response = $this
        ->actingAs($actingUser)
        ->get(route('students.index'));

    $response->assertOk();

    $response->assertInertia(fn ($page) => $page
        ->component('Students/Index')
        ->has('students.data', 1)
    );

    $studentIds = collect($response->viewData('page')['props']['students']['data'])->pluck('id')->toArray();
    expect($studentIds)->toContain($activeStudent->id)
        ->not->toContain($deletedStudent->id);
});

test('students index can filter to show only deleted students', function () {
    $actingUser = User::factory()->create();
    $activeStudent = Student::factory()->create();
    $deletedStudent = Student::factory()->create();
    $deletedStudent->delete();

    $response = $this
        ->actingAs($actingUser)
        ->get(route('students.index', ['with_trashed' => 'only']));

    $response->assertOk();

    $response->assertInertia(fn ($page) => $page
        ->component('Students/Index')
        ->has('students.data', 1)
    );

    $studentIds = collect($response->viewData('page')['props']['students']['data'])->pluck('id')->toArray();
    expect($studentIds)->toContain($deletedStudent->id)
        ->not->toContain($activeStudent->id);
});

test('students index can filter to show all students including deleted', function () {
    $actingUser = User::factory()->create();
    $activeStudent = Student::factory()->create();
    $deletedStudent = Student::factory()->create();
    $deletedStudent->delete();

    $response = $this
        ->actingAs($actingUser)
        ->get(route('students.index', ['with_trashed' => 'all']));

    $response->assertOk();

    $response->assertInertia(fn ($page) => $page
        ->component('Students/Index')
        ->has('students.data', 2)
    );

    $studentIds = collect($response->viewData('page')['props']['students']['data'])->pluck('id')->toArray();
    expect($studentIds)->toContain($activeStudent->id)
        ->toContain($deletedStudent->id);
});

test('student can be restored', function () {
    $actingUser = User::factory()->create();
    $student = Student::factory()->create();
    $student->delete();

    $this->assertSoftDeleted('students', [
        'id' => $student->id,
    ]);

    $response = $this
        ->actingAs($actingUser)
        ->from(route('students.index'))
        ->post(route('students.restore', $student));

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('students.index'));

    $this->assertDatabaseHas('students', [
        'id' => $student->id,
        'deleted_at' => null,
    ]);
});

test('restored student appears in active students list', function () {
    $actingUser = User::factory()->create();
    $student = Student::factory()->create();
    $student->delete();
    $student->restore();

    $response = $this
        ->actingAs($actingUser)
        ->get(route('students.index'));

    $response->assertOk();

    $studentIds = collect($response->viewData('page')['props']['students']['data'])->pluck('id')->toArray();
    expect($studentIds)->toContain($student->id);
});

test('student can be permanently deleted', function () {
    $actingUser = User::factory()->create();
    $student = Student::factory()->create();
    $student->delete();

    $this->assertSoftDeleted('students', [
        'id' => $student->id,
    ]);

    $response = $this
        ->actingAs($actingUser)
        ->from(route('students.index'))
        ->delete(route('students.force-delete', $student));

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('students.index'));

    $this->assertDatabaseMissing('students', [
        'id' => $student->id,
    ]);
});

test('students can be searched by name', function () {
    $actingUser = User::factory()->create();
    $student1 = Student::factory()->create(['name' => 'John Doe']);
    $student2 = Student::factory()->create(['name' => 'Jane Smith']);

    $response = $this
        ->actingAs($actingUser)
        ->get(route('students.index', ['search' => 'John']));

    $response->assertOk();

    $studentIds = collect($response->viewData('page')['props']['students']['data'])->pluck('id')->toArray();
    expect($studentIds)->toContain($student1->id)
        ->not->toContain($student2->id);
});

test('students can be searched by student number', function () {
    $actingUser = User::factory()->create();
    $student1 = Student::factory()->create();
    $student2 = Student::factory()->create();

    $response = $this
        ->actingAs($actingUser)
        ->get(route('students.index', ['search' => $student1->student_number]));

    $response->assertOk();

    $studentIds = collect($response->viewData('page')['props']['students']['data'])->pluck('id')->toArray();
    expect($studentIds)->toContain($student1->id)
        ->not->toContain($student2->id);
});

test('students can be searched by email', function () {
    $actingUser = User::factory()->create();
    $student1 = Student::factory()->create(['email' => 'john@example.com']);
    $student2 = Student::factory()->create(['email' => 'jane@example.com']);

    $response = $this
        ->actingAs($actingUser)
        ->get(route('students.index', ['search' => 'john@example.com']));

    $response->assertOk();

    $studentIds = collect($response->viewData('page')['props']['students']['data'])->pluck('id')->toArray();
    expect($studentIds)->toContain($student1->id)
        ->not->toContain($student2->id);
});
