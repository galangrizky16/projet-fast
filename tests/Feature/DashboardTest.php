<?php

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function createUserWithRole(string $name, string $slug): User
{
    $role = Role::create([
        'nama' => $name,
        'slug' => $slug,
    ]);

    return User::factory()->create([
        'role_id' => $role->id,
    ]);
}

test('guests are redirected to the login page', function () {
    $this->get(route('dashboard'))
        ->assertRedirect(route('login'));
});

test('authenticated users can visit the laravel dashboard page', function () {
    $user = createUserWithRole('Mahasiswa', 'mahasiswa');

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertOk();
});

test('admin users are redirected to the admin dashboard after login redirect route', function () {
    $user = createUserWithRole('Admin', 'admin');

    $this->actingAs($user)
        ->get(route('redirect.dashboard'))
        ->assertRedirect(route('admin.dashboard'));
});

test('mahasiswa users are redirected to the fast user dashboard after login redirect route', function () {
    $user = createUserWithRole('Mahasiswa', 'mahasiswa');

    $this->actingAs($user)
        ->get(route('redirect.dashboard'))
        ->assertRedirect(route('fast.user.dashboard'));
});

test('dosen users are redirected to the fast user dashboard after login redirect route', function () {
    $user = createUserWithRole('Dosen', 'dosen');

    $this->actingAs($user)
        ->get(route('redirect.dashboard'))
        ->assertRedirect(route('fast.user.dashboard'));
});

test('kaprodi users are redirected to the kaprodi dashboard after login redirect route', function () {
    $user = createUserWithRole('Kaprodi', 'kaprodi');

    $this->actingAs($user)
        ->get(route('redirect.dashboard'))
        ->assertRedirect(route('kaprodi.dashboard'));
});

test('dekan users are redirected to the dekan dashboard after login redirect route', function () {
    $user = createUserWithRole('Dekan', 'dekan');

    $this->actingAs($user)
        ->get(route('redirect.dashboard'))
        ->assertRedirect(route('dekan.dashboard'));
});

test('admin users cannot access the fast user dashboard directly', function () {
    $user = createUserWithRole('Admin', 'admin');

    $this->actingAs($user)
        ->get(route('fast.user.dashboard'))
        ->assertForbidden();
});

test('mahasiswa users cannot access the admin dashboard directly', function () {
    $user = createUserWithRole('Mahasiswa', 'mahasiswa');

    $this->actingAs($user)
        ->get(route('admin.dashboard'))
        ->assertForbidden();
});

test('mahasiswa users cannot access the approval dashboard directly', function () {
    $user = createUserWithRole('Mahasiswa', 'mahasiswa');

    $this->actingAs($user)
        ->get(route('approval.dashboard'))
        ->assertForbidden();
});
