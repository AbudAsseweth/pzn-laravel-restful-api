<?php

namespace Tests\Unit;

use App\Http\Resources\ContactResource;
use App\Models\Contact;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

class ContactTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_may_create_new_contact()
    {
        User::factory()->create([
            "username" => "abud",
            "password" => "password",
            "name" => "yazid",
            "token" => "unique"
        ]);

        $header = [
            "authorization" => "unique",
        ];

        $contact = [
            "first_name" => "Muhammad",
            "last_name" => "Asseweth",
            "email" => "yazid@gmail.com",
            "phone" => "082167361472"
        ];

        $this->postJson("/api/contacts", $contact, $header)
            ->assertStatus(201)
            ->assertJson([
                "data" => $contact
            ]);
    }

    public function test_authenticated_user_validation_failed_cannot_create_contact()
    {
        User::factory()->create([
            "username" => "abud",
            "password" => "password",
            "name" => "yazid",
            "token" => "unique"
        ]);

        $header = [
            "authorization" => "unique",
        ];

        $contact = [];

        $this->postJson("/api/contacts", $contact, $header)
            ->assertStatus(422)
            ->assertJsonValidationErrors([
                "first_name",
                "last_name",
                "email",
                "phone"
            ]);
    }

    public function test_unauthenticated_user_may_not_create_new_contact()
    {
        User::factory()->create([
            "username" => "abud",
            "password" => "password",
            "name" => "yazid",
        ]);

        $contact = [
            "first_name" => "Muhammad",
            "last_name" => "Asseweth",
            "email" => "yazid@gmail.com",
            "phone" => "082167361472"
        ];;

        $this->postJson("/api/contacts", $contact)
            ->assertStatus(401)
            ->assertJsonValidationErrorFor("message");
    }

    public function test_user_may_get_specific_contact_with_id()
    {
        User::factory()->create([
            "username" => "abud",
            "password" => "password",
            "name" => "yazid",
            "token" => "unique",
        ]);

        $contactData = [
            "first_name" => "Muhammad",
            "last_name" => "Asseweth",
            "email" => "yazid@gmail.com",
            "phone" => "082167361472"
        ];

        $contactData['user_id'] = 1;

        $contact = Contact::factory()->create(
            $contactData
        );

        $headers = [
            "authorization" => "unique",
        ];

        $this->getJson("/api/contacts/{$contact->id}", headers: $headers)
            ->assertStatus(200)
            ->assertJson([
                "data" => [
                    "id" => 1,
                    "first_name" => "Muhammad",
                    "last_name" => "Asseweth",
                    "email" => "yazid@gmail.com",
                    "phone" => "082167361472"
                ]
            ]);
    }

    public function test_user_cannot_fetch_unexist_contact()
    {
        User::factory()->create([
            "username" => "abud",
            "password" => "password",
            "name" => "yazid",
            "token" => "unique",
        ]);

        $headers = [
            "authorization" => "unique",
        ];

        $this->getJson("/api/contacts/1", headers: $headers)
            ->assertStatus(404)
            ->assertJson([
                "message" => "Record not found."
            ]);
    }

    public function test_user_cannot_fetch_other_user_contact()
    {
        User::factory()->create([
            "username" => "abud",
            "password" => "password",
            "name" => "yazid",
            "token" => "unique",
        ]);

        User::factory()->create([
            "username" => "asseweth",
            "password" => "password",
            "name" => "yazid",
            "token" => "user2",
        ]);

        $contactData = [
            "first_name" => "Muhammad",
            "last_name" => "Asseweth",
            "email" => "yazid@gmail.com",
            "phone" => "082167361472"
        ];

        $contactData['user_id'] = 1;

        Contact::factory($contactData);

        $headers = [
            "authorization" => "user2",
        ];

        $this->getJson("/api/contacts/1", headers: $headers)
            ->assertStatus(404)
            ->assertJson([
                "message" => "Record not found."
            ]);
    }

    public function test_user_may_update_their_contact()
    {
        User::factory()->create([
            "username" => "abud",
            "password" => "password",
            "name" => "yazid",
            "token" => "unique",
        ]);

        $contactData = [
            "first_name" => "Muhammad",
            "last_name" => "Asseweth",
            "email" => "yazid@gmail.com",
            "phone" => "082167361472"
        ];

        $contactData['user_id'] = 1;

        Contact::factory($contactData)->create();

        $contactDataUpdated = [
            "first_name" => "Muhammad-updated",
            "last_name" => "Asseweth-updated",
            "email" => "yazid@gmail.com-updated",
            "phone" => "082167361472-updated"
        ];

        $headers = [
            "authorization" => "unique",
        ];

        $this->patchJson("/api/contacts/1", $contactDataUpdated, $headers)
            ->assertStatus(200)
            ->assertJson([
                "data" => [
                    "first_name" => "Muhammad-updated",
                    "last_name" => "Asseweth-updated",
                    "email" => "yazid@gmail.com-updated",
                    "phone" => "082167361472-updated"
                ]
            ]);
    }
}
