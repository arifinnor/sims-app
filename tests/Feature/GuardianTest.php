<?php

use App\Models\Guardian;
use App\Models\User;

beforeEach(function () {
    config([
        'app.key' => 'base64:'.base64_encode(random_bytes(32)),
    ]);
});

test('guardians index page is displayed', function () {
    $actingUser = User::factory()->create();
    Guardian::factory(3)->create();

    $response = $this
        ->actingAs($actingUser)
        ->get(route('guardians.index'));

    $response->assertOk();
});

test('guardian can be created', function () {
    $actingUser = User::factory()->create();

    $response = $this
        ->actingAs($actingUser)
        ->from(route('guardians.create'))
        ->post(route('guardians.store'), [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'phone' => '+1234567890',
            'relationship' => 'Father',
            'address' => '123 Main St',
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('guardians.index'));

    $this->assertDatabaseHas('guardians', [
        'email' => 'john@example.com',
        'name' => 'John Doe',
    ]);
});

test('guardian can be created without email', function () {
    $actingUser = User::factory()->create();

    $response = $this
        ->actingAs($actingUser)
        ->from(route('guardians.create'))
        ->post(route('guardians.store'), [
            'name' => 'Jane Doe',
            'email' => null,
            'phone' => null,
            'relationship' => null,
            'address' => null,
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('guardians.index'));

    $this->assertDatabaseHas('guardians', [
        'name' => 'Jane Doe',
        'email' => null,
    ]);
});

test('guardian creation validates unique email', function () {
    $actingUser = User::factory()->create();
    $existingGuardian = Guardian::factory()->create(['email' => 'john@example.com']);

    $response = $this
        ->actingAs($actingUser)
        ->from(route('guardians.create'))
        ->post(route('guardians.store'), [
            'name' => 'John Duplicate',
            'email' => $existingGuardian->email,
            'phone' => '+1234567890',
        ]);

    $response
        ->assertSessionHasErrors('email')
        ->assertRedirect(route('guardians.create'));

    $this->assertDatabaseCount('guardians', 1);
});

test('guardian can be updated', function () {
    $actingUser = User::factory()->create();
    $guardian = Guardian::factory()->create([
        'name' => 'John Doe',
        'email' => 'john@example.com',
    ]);

    $response = $this
        ->actingAs($actingUser)
        ->from(route('guardians.edit', $guardian))
        ->put(route('guardians.update', $guardian), [
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'phone' => '+9876543210',
            'relationship' => 'Mother',
            'address' => '456 Oak Ave',
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('guardians.edit', $guardian));

    $this->assertDatabaseHas('guardians', [
        'id' => $guardian->id,
        'name' => 'Jane Doe',
        'email' => 'jane@example.com',
    ]);
});

test('guardian update validates unique email ignoring current guardian', function () {
    $actingUser = User::factory()->create();
    $guardian1 = Guardian::factory()->create(['email' => 'john@example.com']);
    $guardian2 = Guardian::factory()->create(['email' => 'jane@example.com']);

    $response = $this
        ->actingAs($actingUser)
        ->from(route('guardians.edit', $guardian1))
        ->put(route('guardians.update', $guardian1), [
            'name' => 'John Doe',
            'email' => $guardian2->email,
            'phone' => '+1234567890',
        ]);

    $response
        ->assertSessionHasErrors('email')
        ->assertRedirect(route('guardians.edit', $guardian1));

    $this->assertDatabaseHas('guardians', [
        'id' => $guardian1->id,
        'email' => 'john@example.com',
    ]);
});

test('guardian can be soft deleted', function () {
    $actingUser = User::factory()->create();
    $guardian = Guardian::factory()->create();

    $response = $this
        ->actingAs($actingUser)
        ->delete(route('guardians.destroy', $guardian));

    $response->assertRedirect(route('guardians.index'));

    $this->assertSoftDeleted('guardians', [
        'id' => $guardian->id,
    ]);
});

test('guardians can be filtered by active status', function () {
    $actingUser = User::factory()->create();
    $activeGuardian = Guardian::factory()->create();
    $deletedGuardian = Guardian::factory()->create();
    $deletedGuardian->delete();

    $response = $this
        ->actingAs($actingUser)
        ->get(route('guardians.index', ['with_trashed' => 'none']));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('Guardians/Index')
        ->has('guardians.data', 1)
        ->where('guardians.data.0.id', $activeGuardian->id)
    );
});

test('guardians can be filtered to show only deleted', function () {
    $actingUser = User::factory()->create();
    $activeGuardian = Guardian::factory()->create();
    $deletedGuardian = Guardian::factory()->create();
    $deletedGuardian->delete();

    $response = $this
        ->actingAs($actingUser)
        ->get(route('guardians.index', ['with_trashed' => 'only']));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('Guardians/Index')
        ->has('guardians.data', 1)
        ->where('guardians.data.0.id', $deletedGuardian->id)
    );
});

test('guardians can be filtered to show all', function () {
    $actingUser = User::factory()->create();
    $activeGuardian = Guardian::factory()->create();
    $deletedGuardian = Guardian::factory()->create();
    $deletedGuardian->delete();

    $response = $this
        ->actingAs($actingUser)
        ->get(route('guardians.index', ['with_trashed' => 'all']));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('Guardians/Index')
        ->has('guardians.data', 2)
    );
});

test('guardian can be restored', function () {
    $actingUser = User::factory()->create();
    $guardian = Guardian::factory()->create();
    $guardian->delete();

    $response = $this
        ->actingAs($actingUser)
        ->post(route('guardians.restore', $guardian->id));

    $response->assertRedirect(route('guardians.index'));

    $this->assertDatabaseHas('guardians', [
        'id' => $guardian->id,
        'deleted_at' => null,
    ]);
});

test('guardian can be permanently deleted', function () {
    $actingUser = User::factory()->create();
    $guardian = Guardian::factory()->create();
    $guardian->delete();

    $response = $this
        ->actingAs($actingUser)
        ->delete(route('guardians.force-delete', $guardian->id));

    $response->assertRedirect(route('guardians.index'));

    $this->assertDatabaseMissing('guardians', [
        'id' => $guardian->id,
    ]);
});

test('guardians can be searched by name', function () {
    $actingUser = User::factory()->create();
    $guardian1 = Guardian::factory()->create(['name' => 'John Doe']);
    $guardian2 = Guardian::factory()->create(['name' => 'Jane Smith']);

    $response = $this
        ->actingAs($actingUser)
        ->get(route('guardians.index', ['search' => 'John']));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('Guardians/Index')
        ->has('guardians.data', 1)
        ->where('guardians.data.0.id', $guardian1->id)
    );
});

test('guardians can be searched by email', function () {
    $actingUser = User::factory()->create();
    $guardian1 = Guardian::factory()->create(['email' => 'john@example.com']);
    $guardian2 = Guardian::factory()->create(['email' => 'jane@example.com']);

    $response = $this
        ->actingAs($actingUser)
        ->get(route('guardians.index', ['search' => 'john@example.com']));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('Guardians/Index')
        ->has('guardians.data', 1)
        ->where('guardians.data.0.id', $guardian1->id)
    );
});

test('guardians can be searched by phone', function () {
    $actingUser = User::factory()->create();
    $guardian1 = Guardian::factory()->create(['phone' => '+1234567890']);
    $guardian2 = Guardian::factory()->create(['phone' => '+9876543210']);

    $response = $this
        ->actingAs($actingUser)
        ->get(route('guardians.index', ['search' => '1234567890']));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('Guardians/Index')
        ->has('guardians.data', 1)
        ->where('guardians.data.0.id', $guardian1->id)
    );
});

test('guardians can be searched by relationship', function () {
    $actingUser = User::factory()->create();
    $guardian1 = Guardian::factory()->create(['relationship' => 'Father']);
    $guardian2 = Guardian::factory()->create(['relationship' => 'Mother']);

    $response = $this
        ->actingAs($actingUser)
        ->get(route('guardians.index', ['search' => 'Father']));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('Guardians/Index')
        ->has('guardians.data', 1)
        ->where('guardians.data.0.id', $guardian1->id)
    );
});
