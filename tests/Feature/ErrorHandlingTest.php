<?php

namespace Tests\Feature;

use App\Models\Contact;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\JsonResponse;
use Tests\TestCase;

class ErrorHandlingTest extends TestCase
{
    use RefreshDatabase;

    public function test_returns_404_for_nonexistent_contact()
    {
        $user = User::factory()->create();
        
        $response = $this->actingAs($user)
            ->getJson('/api/contacts/999');

        $response->assertStatus(404)
            ->assertJsonStructure([
                'error',
                'status',
                'timestamp'
            ]);
    }

    public function test_returns_422_for_invalid_contact_data()
    {
        $user = User::factory()->create();
        
        $response = $this->actingAs($user)
            ->postJson('/api/contacts', [
                'name' => '', // Invalid: empty name
                'email' => 'invalid-email', // Invalid email format
                'phone' => '123' // Too short
            ]);

        $response->assertStatus(422)
            ->assertJsonStructure([
                'error',
                'status',
                'timestamp'
            ]);
    }

    public function test_returns_401_for_unauthenticated_requests()
    {
        $response = $this->getJson('/api/contacts');

        $response->assertStatus(401);
    }

    public function test_returns_404_for_nonexistent_endpoint()
    {
        $user = User::factory()->create();
        
        $response = $this->actingAs($user)
            ->getJson('/api/nonexistent-endpoint');

        $response->assertStatus(404)
            ->assertJsonStructure([
                'error',
                'status',
                'timestamp'
            ]);
    }

    public function test_successful_operations_return_proper_structure()
    {
        $user = User::factory()->create();
        $contact = Contact::factory()->create();
        
        $response = $this->actingAs($user)
            ->getJson("/api/contacts/{$contact->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data',
                'message'
            ]);
    }

    public function test_database_errors_are_handled_gracefully()
    {
        $user = User::factory()->create();
        
        // This test simulates a database error by trying to create a contact
        // with duplicate email which would cause a database constraint violation
        Contact::factory()->create(['email' => 'test@example.com']);
        
        $response = $this->actingAs($user)
            ->postJson('/api/contacts', [
                'name' => 'Test Contact',
                'email' => 'test@example.com', // Duplicate email
                'phone' => '1234567890'
            ]);

        // Should return a 422 error for validation (duplicate email)
        $response->assertStatus(422)
            ->assertJsonStructure([
                'error',
                'status',
                'timestamp'
            ]);
    }
} 