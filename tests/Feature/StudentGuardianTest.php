<?php

use App\Models\Guardian;
use App\Models\Student;
use App\Models\User;

beforeEach(function () {
    config([
        'app.key' => 'base64:'.base64_encode(random_bytes(32)),
    ]);
});

test('guardian can be attached to student', function () {
    $actingUser = User::factory()->create();
    $student = Student::factory()->create();
    $guardian = Guardian::factory()->create();

    $response = $this
        ->actingAs($actingUser)
        ->post(route('students.guardians.attach', $student), [
            'guardian_id' => $guardian->id,
            'relationship_type' => 'Father',
            'is_primary' => true,
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('students.show', $student));

    $this->assertDatabaseHas('student_guardian', [
        'student_id' => $student->id,
        'guardian_id' => $guardian->id,
        'relationship_type' => 'Father',
        'is_primary' => true,
    ]);
});

test('guardian cannot be attached to same student twice', function () {
    $actingUser = User::factory()->create();
    $student = Student::factory()->create();
    $guardian = Guardian::factory()->create();
    $student->guardians()->attach($guardian->id, [
        'relationship_type' => 'Mother',
        'is_primary' => false,
    ]);

    $response = $this
        ->actingAs($actingUser)
        ->post(route('students.guardians.attach', $student), [
            'guardian_id' => $guardian->id,
            'relationship_type' => 'Father',
            'is_primary' => false,
        ]);

    $response
        ->assertSessionHasErrors('guardian_id')
        ->assertRedirect();

    $this->assertDatabaseCount('student_guardian', 1);
});

test('primary guardian validation prevents multiple primary guardians', function () {
    $actingUser = User::factory()->create();
    $student = Student::factory()->create();
    $guardian1 = Guardian::factory()->create();
    $guardian2 = Guardian::factory()->create();

    $student->guardians()->attach($guardian1->id, [
        'relationship_type' => 'Mother',
        'is_primary' => true,
    ]);

    $response = $this
        ->actingAs($actingUser)
        ->post(route('students.guardians.attach', $student), [
            'guardian_id' => $guardian2->id,
            'relationship_type' => 'Father',
            'is_primary' => true,
        ]);

    $response
        ->assertSessionHasErrors('is_primary')
        ->assertRedirect();

    $this->assertDatabaseHas('student_guardian', [
        'student_id' => $student->id,
        'guardian_id' => $guardian1->id,
        'is_primary' => true,
    ]);
    $this->assertDatabaseMissing('student_guardian', [
        'student_id' => $student->id,
        'guardian_id' => $guardian2->id,
    ]);
});

test('guardian can be detached from student', function () {
    $actingUser = User::factory()->create();
    $student = Student::factory()->create();
    $guardian = Guardian::factory()->create();
    $student->guardians()->attach($guardian->id, [
        'relationship_type' => 'Father',
        'is_primary' => false,
    ]);

    $response = $this
        ->actingAs($actingUser)
        ->delete(route('students.guardians.detach', [$student, $guardian]));

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('students.show', $student));

    $this->assertDatabaseMissing('student_guardian', [
        'student_id' => $student->id,
        'guardian_id' => $guardian->id,
    ]);
});

test('guardian relationship can be updated', function () {
    $actingUser = User::factory()->create();
    $student = Student::factory()->create();
    $guardian = Guardian::factory()->create();
    $student->guardians()->attach($guardian->id, [
        'relationship_type' => 'Father',
        'is_primary' => false,
    ]);

    $response = $this
        ->actingAs($actingUser)
        ->put(route('students.guardians.update', [$student, $guardian]), [
            'relationship_type' => 'Legal Guardian',
            'is_primary' => true,
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('students.show', $student));

    $this->assertDatabaseHas('student_guardian', [
        'student_id' => $student->id,
        'guardian_id' => $guardian->id,
        'relationship_type' => 'Legal Guardian',
        'is_primary' => true,
    ]);
});

test('primary guardian can be changed', function () {
    $actingUser = User::factory()->create();
    $student = Student::factory()->create();
    $guardian1 = Guardian::factory()->create();
    $guardian2 = Guardian::factory()->create();

    $student->guardians()->attach($guardian1->id, [
        'relationship_type' => 'Mother',
        'is_primary' => true,
    ]);
    $student->guardians()->attach($guardian2->id, [
        'relationship_type' => 'Father',
        'is_primary' => false,
    ]);

    // First, unset primary on guardian1
    $student->guardians()->updateExistingPivot($guardian1->id, [
        'is_primary' => false,
    ]);

    // Then set primary on guardian2
    $response = $this
        ->actingAs($actingUser)
        ->put(route('students.guardians.update', [$student, $guardian2]), [
            'relationship_type' => 'Father',
            'is_primary' => true,
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('students.show', $student));

    $this->assertDatabaseHas('student_guardian', [
        'student_id' => $student->id,
        'guardian_id' => $guardian1->id,
        'is_primary' => false,
    ]);
    $this->assertDatabaseHas('student_guardian', [
        'student_id' => $student->id,
        'guardian_id' => $guardian2->id,
        'is_primary' => true,
    ]);
});

test('cannot set primary guardian when another is already primary', function () {
    $actingUser = User::factory()->create();
    $student = Student::factory()->create();
    $guardian1 = Guardian::factory()->create();
    $guardian2 = Guardian::factory()->create();

    $student->guardians()->attach($guardian1->id, [
        'relationship_type' => 'Mother',
        'is_primary' => true,
    ]);
    $student->guardians()->attach($guardian2->id, [
        'relationship_type' => 'Father',
        'is_primary' => false,
    ]);

    $response = $this
        ->actingAs($actingUser)
        ->put(route('students.guardians.update', [$student, $guardian2]), [
            'relationship_type' => 'Father',
            'is_primary' => true,
        ]);

    $response
        ->assertSessionHasErrors('is_primary')
        ->assertRedirect();

    $this->assertDatabaseHas('student_guardian', [
        'student_id' => $student->id,
        'guardian_id' => $guardian1->id,
        'is_primary' => true,
    ]);
    $this->assertDatabaseHas('student_guardian', [
        'student_id' => $student->id,
        'guardian_id' => $guardian2->id,
        'is_primary' => false,
    ]);
});

test('student guardians are loaded with student', function () {
    $actingUser = User::factory()->create();
    $student = Student::factory()->create();
    $guardian1 = Guardian::factory()->create();
    $guardian2 = Guardian::factory()->create();

    $student->guardians()->attach($guardian1->id, [
        'relationship_type' => 'Mother',
        'is_primary' => true,
    ]);
    $student->guardians()->attach($guardian2->id, [
        'relationship_type' => 'Father',
        'is_primary' => false,
    ]);

    $response = $this
        ->actingAs($actingUser)
        ->get(route('students.show', $student));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('Students/Show')
        ->has('student.guardians', 2)
        ->where('student.guardians.0.id', $guardian1->id)
        ->where('student.guardians.0.pivot.isPrimary', true)
        ->where('student.guardians.1.id', $guardian2->id)
        ->where('student.guardians.1.pivot.isPrimary', false)
    );
});

test('guardian students are loaded with guardian', function () {
    $actingUser = User::factory()->create();
    $guardian = Guardian::factory()->create();
    $student1 = Student::factory()->create();
    $student2 = Student::factory()->create();

    $student1->guardians()->attach($guardian->id, [
        'relationship_type' => 'Mother',
        'is_primary' => true,
    ]);
    $student2->guardians()->attach($guardian->id, [
        'relationship_type' => 'Father',
        'is_primary' => true,
    ]);

    $guardian->load('students');

    expect($guardian->students)->toHaveCount(2);
    expect($guardian->students->pluck('id')->toArray())
        ->toContain($student1->id)
        ->toContain($student2->id);
});

test('guardian can have multiple students', function () {
    $actingUser = User::factory()->create();
    $guardian = Guardian::factory()->create();
    $student1 = Student::factory()->create();
    $student2 = Student::factory()->create();
    $student3 = Student::factory()->create();

    $student1->guardians()->attach($guardian->id);
    $student2->guardians()->attach($guardian->id);
    $student3->guardians()->attach($guardian->id);

    $guardian->load('students');

    expect($guardian->students)->toHaveCount(3);
});

test('student can have multiple guardians', function () {
    $actingUser = User::factory()->create();
    $student = Student::factory()->create();
    $guardian1 = Guardian::factory()->create();
    $guardian2 = Guardian::factory()->create();
    $guardian3 = Guardian::factory()->create();

    $student->guardians()->attach($guardian1->id);
    $student->guardians()->attach($guardian2->id);
    $student->guardians()->attach($guardian3->id);

    $student->load('guardians');

    expect($student->guardians)->toHaveCount(3);
});
