<?php

namespace Tests\Feature;

use App\Models\Contact;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ContactTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected User $user;
    protected string $token;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Criar usuário e token para todos os testes
        $this->user = User::factory()->create();
        $this->token = $this->user->createToken('test_token')->plainTextToken;
    }

    public function test_can_create_contact(): void
    {
        $contactData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'phone' => '1234567890'
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->postJson('/api/contacts', $contactData);

        $response->assertStatus(201)
                ->assertJsonStructure([
                    'data' => [
                        'id',
                        'name',
                        'email',
                        'phone',
                        'score',
                        'processed_at',
                        'created_at',
                        'updated_at'
                    ]
                ]);

        $this->assertDatabaseHas('contacts', $contactData);
    }

    public function test_can_list_contacts(): void
    {
        Contact::factory()->count(3)->create();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->getJson('/api/contacts');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'data' => [
                        '*' => [
                            'id',
                            'name',
                            'email',
                            'phone',
                            'score',
                            'processed_at',
                            'created_at',
                            'updated_at'
                        ]
                    ],
                    'meta' => [
                        'total',
                        'per_page',
                        'current_page',
                        'last_page'
                    ]
                ]);
    }

    public function test_can_show_contact(): void
    {
        $contact = Contact::factory()->create();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->getJson("/api/contacts/{$contact->id}");

        $response->assertStatus(200)
                ->assertJson([
                    'data' => [
                        'id' => $contact->id,
                        'name' => $contact->name,
                        'email' => $contact->email,
                        'phone' => $contact->phone
                    ]
                ]);
    }

    public function test_can_update_contact(): void
    {
        $contact = Contact::factory()->create();
        $updateData = [
            'name' => 'Updated Name',
            'email' => 'updated@example.com'
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->putJson("/api/contacts/{$contact->id}", $updateData);

        $response->assertStatus(200)
                ->assertJson([
                    'data' => [
                        'name' => 'Updated Name',
                        'email' => 'updated@example.com'
                    ]
                ]);
    }

    public function test_can_delete_contact(): void
    {
        $contact = Contact::factory()->create();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->deleteJson("/api/contacts/{$contact->id}");

        $response->assertStatus(200)
                ->assertJson(['message' => 'Contact deleted successfully']);

        $this->assertSoftDeleted('contacts', ['id' => $contact->id]);
    }

    public function test_can_process_contact_score(): void
    {
        $contact = Contact::factory()->create(['score' => 0]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->postJson("/api/contacts/{$contact->id}/process-score");

        $response->assertStatus(200)
                ->assertJson([
                    'message' => 'Score processing started',
                    'contact_id' => $contact->id
                ]);
    }
}
