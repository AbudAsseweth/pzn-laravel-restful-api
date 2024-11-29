<?php

namespace Tests\Unit;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic unit test example.
     */
    public function test_user_can_register()
    {
        $user = User::factory()->make([
            "username" => "abud",
            "password" => "password",
            "name" => "Muhammad Yazid Abud Asseweth"
        ]);

        $this->post("/api/users", $user->toArray())->assertStatus(201)->assertJson([
            "data" => [
                "username" => 'abud',
                "name" => "Muhammad Yazid Abud Asseweth"
            ]
        ]);
    }

    public function test_register_user_failed_because_validation_fields()
    {
        $user = User::factory()->make([
            "username" => "",
            "password" => "",
        ]);

        $this->post("/api/users", $user->toArray())
            ->assertStatus(400)
            ->assertJson(
                [
                    "errors" => [
                        "username" => [
                            "The username field is required."
                        ],
                        "password" => [
                            "The password field is required."
                        ]
                    ]
                ]
            );
    }

    public function test_register_user_failed_because_user_already_registered()
    {
        User::factory()->create([
            "username" => "abud",
            "password" => "password",
            "name" => "Muhammad Yazid Abud Asseweth"
        ]);

        $user = User::factory()->make([
            "username" => "abud",
            "password" => "password",
        ]);

        $this->post("/api/users", $user->toArray())
            ->assertStatus(400)
            ->assertJson(
                [
                    "errors" => [
                        "username" => [
                            "The username has already been taken."
                        ],
                    ]
                ]
            );
    }

    public function test_user_may_login_with_registered_credentials()
    {
        User::factory()->create([
            "username" => "abud",
            "password" => Hash::make("password"),
            "name" => "Muhammad Yazid Abud Asseweth"
        ]);

        $user = User::factory()->make([
            "username" => "abud",
            "password" => "password",
        ]);

        $this->post("/api/users/login", $user->toArray())
            ->assertStatus(200)
            ->assertJson(
                [
                    "data" => [
                        "username" => "abud",
                        "name" => "Muhammad Yazid Abud Asseweth",
                    ]
                ]
            );


        $userDb = User::all()->first();
        $this->assertNotNull($userDb->token);
    }

    public function test_user_cannot_login_with_unregistered_credentials()
    {
        $user = User::factory()->make([
            "username" => "abud",
            "password" => "password",
        ]);

        $this->post("/api/users/login", $user->toArray())
            ->assertStatus(401)
            ->assertJson(
                [
                    "errors" => [
                        "username" => "Your credentials is not match."
                    ]
                ]
            );
    }

    public function test_user_cannot_login_with_wrong_password()
    {
        User::factory()->create([
            "username" => "abud",
            "password" => Hash::make("password"),
            "name" => "Muhammad Yazid Abud Asseweth"
        ]);

        $user = User::factory()->make([
            "username" => "abud",
            "password" => "rahasia",
        ]);

        $this->post("/api/users/login", $user->toArray())
            ->assertStatus(401)
            ->assertJson(
                [
                    "errors" => [
                        "username" => "Your credentials is not match."
                    ]
                ]
            );
    }

    public function test_success_get_current_user()
    {
        User::factory()->create([
            "username" => "abud",
            "password" => Hash::make("password"),
            "name" => "Yazid",
            "token" => "test",
        ]);

        $this->get("/api/users/current", [
            'authorization' => "test",
        ])->assertStatus(200)
            ->assertJson(
                [
                    "data" => [
                        "username" => "abud",
                        "name" => "Yazid",
                    ]
                ]
            );
    }

    public function test_unauthorized_get_current_user_with_wrong_token()
    {
        User::factory()->create([
            "username" => "abud",
            "password" => Hash::make("password"),
            "name" => "Yazid",
            "token" => "test",
        ]);

        $this->get("/api/users/current", [
            'authorization' => "salah",
        ])->assertStatus(401)
            ->assertJson(
                [
                    "errors" => [
                        "message" => "This action is unauthorized",
                    ]
                ]
            );
    }

    public function test_unauthorized_get_current_user_without_auhorization_header()
    {
        User::factory()->create([
            "username" => "abud",
            "password" => Hash::make("password"),
            "name" => "Yazid",
            "token" => "test",
        ]);

        $this->get("/api/users/current")->assertStatus(401)
            ->assertJson(
                [
                    "errors" => [
                        "message" => "This action is unauthorized",
                    ]
                ]
            );
    }

    public function test_success_update_user_username()
    {
        User::factory()->create([
            "username" => "abud",
            "password" => Hash::make("password"),
            "name" => "Yazid",
            "token" => "test",
        ]);

        $this->patch("/api/users/current", [
            "username" => "asseweth",
        ], [
            "authorization" => "test",
        ])->assertStatus(200)
            ->assertJson(
                [
                    "data" => [
                        "username" => "asseweth",
                    ]
                ]
            );
    }

    public function test_success_update_user_password()
    {
        $newPassword = "rahasia";

        User::factory()->create([
            "username" => "abud",
            "password" => Hash::make("password"),
            "name" => "Yazid",
            "token" => "test",
        ]);

        $this->patch("/api/users/current", [
            "password" => $newPassword,
        ], [
            "authorization" => "test",
        ])->assertStatus(200)
            ->assertJson(
                [
                    "data" => [
                        "username" => "abud",
                        "name" => "Yazid",
                    ]
                ]
            );

        $user = User::get()->first();
        $this->assertEquals(true, Hash::check($newPassword, $user->password));
    }

    public function test_failed_validation_update_user()
    {
        User::factory()->create([
            "username" => "abud",
            "password" => Hash::make("password"),
            "name" => "Yazid",
            "token" => "test",
        ]);

        $this->patchJson("/api/users/current", [
            "username" => fake()->text(150),
        ], [
            "authorization" => "test",
        ])->assertStatus(422)->assertJsonValidationErrorFor('username');
    }
}
