<?php

namespace Tests;

use App\Models\User;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

abstract class TestCase extends BaseTestCase
{
    use RefreshDatabase;

    /**
     * Create a super admin user for testing.
     */
    protected function createSuperAdmin(array $attributes = []): User
    {
        return User::factory()->superAdmin()->create($attributes);
    }

    /**
     * Create a content admin user for testing.
     */
    protected function createContentAdmin(array $attributes = []): User
    {
        return User::factory()->contentAdmin()->create($attributes);
    }

    /**
     * Create an event admin user for testing.
     */
    protected function createEventAdmin(array $attributes = []): User
    {
        return User::factory()->eventAdmin()->create($attributes);
    }

    /**
     * Create a member user for testing.
     */
    protected function createMember(array $attributes = []): User
    {
        return User::factory()->member()->create($attributes);
    }

    /**
     * Act as a super admin user.
     */
    protected function actingAsSuperAdmin(array $attributes = []): static
    {
        return $this->actingAs($this->createSuperAdmin($attributes));
    }

    /**
     * Act as a content admin user.
     */
    protected function actingAsContentAdmin(array $attributes = []): static
    {
        return $this->actingAs($this->createContentAdmin($attributes));
    }

    /**
     * Act as an event admin user.
     */
    protected function actingAsEventAdmin(array $attributes = []): static
    {
        return $this->actingAs($this->createEventAdmin($attributes));
    }

    /**
     * Act as a member user.
     */
    protected function actingAsMember(array $attributes = []): static
    {
        return $this->actingAs($this->createMember($attributes));
    }

    /**
     * Assert that the response has validation errors for the given fields.
     */
    protected function assertValidationErrors(array $fields): void
    {
        $this->assertSessionHasErrors($fields);
    }

    /**
     * Assert that the user is redirected to login.
     */
    protected function assertRedirectToLogin(): void
    {
        $this->assertRedirect(route('login'));
    }

    /**
     * Assert that the response is forbidden (403).
     */
    protected function assertForbidden(): void
    {
        $this->assertStatus(403);
    }
}
