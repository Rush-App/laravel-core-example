<?php

namespace Tests\Feature\Post;

use App\Models\Category;
use App\Models\Post\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Tests\BaseFeatureTest;

class PostExpandTest extends BaseFeatureTest
{
    use RefreshDatabase;

    protected string $entity = 'posts';

    /** @test */
    public function indexWithRelationsTest()
    {
        $this->signIn()->assignAllActionsForAdminUser($this->entity);

        /** @var Post[]|Collection $posts */
        $posts = Post::factory()->count(10)->create();

        $response = $this->json('GET', $this->entity, [
            'with' => 'user|category',
        ]);

        $response
            ->assertOk()
            ->assertJsonStructure([
                '*' => [
                    "id",
                    "title",
                    "description",
                    "published",
                    "user_id",
                    "created_at",
                    "updated_at",
                    "user" => [
                        "id",
                        "name",
                        "email",
                        "email_verified_at",
                        "created_at",
                        "updated_at",
                      ],
                      "category" => [
                          "id",
                          "name",
                          "created_at",
                          "updated_at",
                      ]
                ]
            ]);
    }

    /** @test */
    public function indexWithRelationsAndDefinedFieldsTest()
    {
        $this->signIn()->assignAllActionsForAdminUser($this->entity);

        /** @var Post[]|Collection $posts */
        $posts = Post::factory()->count(10)->create();

        $response = $this->json('GET', $this->entity, [
            'with' => 'user:id,name,email|category:id,name,updated_at',
        ]);

        $response
            ->assertOk()
            ->assertJsonStructure([
                '*' => [
                    "id",
                    "title",
                    "description",
                    "published",
                    "user_id",
                    "created_at",
                    "updated_at",
                    "user" => [
                        "id",
                        "name",
                        "email",
                    ],
                    "category" => [
                        "id",
                        "name",
                        'updated_at',
                    ]
                ]
            ]);

        $sqlQueryNumber = 0;
        DB::listen(function($query) use (&$sqlQueryNumber) {
            $sqlQueryNumber++;
        });

        $response = $this->json('GET', $this->entity, [
            'with' => 'user:id,name,email|categories:id,name,updated_at',
        ]);
        $this->assertLessThanOrEqual(3, $sqlQueryNumber);
    }
}