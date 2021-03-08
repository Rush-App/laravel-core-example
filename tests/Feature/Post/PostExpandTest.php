<?php

namespace Tests\Feature\Post;

use App\Models\Category;
use App\Models\Post\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
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
        /** @var Category[]|Collection $categories */
        $categories = Category::factory()->count(5)->create();

        foreach ($posts as $post) {
            $post->categories()->saveMany($categories->random(2));
        }

        $response = $this->json('GET', $this->entity, [
            'with' => 'user|categories',
        ]);

        $response
            ->assertOk()
            ->assertJsonStructure([
                '*' => [
                    "id",
                    "title",
                    "description",
                    "post_id",
                    "language_id",
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
                      "categories" => [
                          '*' => [
                              "id",
                              "name",
                              "created_at",
                              "updated_at",
                              "pivot" => [
                                  'post_id',
                                  'category_id',
                              ],
                          ],
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
        /** @var Category[]|Collection $categories */
        $categories = Category::factory()->count(5)->create();

        foreach ($posts as $post) {
            $post->categories()->saveMany($categories->random(2));
        }

        $response = $this->json('GET', $this->entity, [
            'with' => 'user:id,name,email|categories:id,name,updated_at',
        ]);

        $response
            ->assertOk()
            ->assertJsonStructure([
                '*' => [
                    "id",
                    "title",
                    "description",
                    "post_id",
                    "language_id",
                    "published",
                    "user_id",
                    "created_at",
                    "updated_at",
                    "user" => [
                        "id",
                        "name",
                        "email",
                    ],
                    "categories" => [
                        '*' => [
                            "id",
                            "name",
                            "updated_at",
                            "pivot" => [
                                'post_id',
                                'category_id',
                            ],
                        ],
                    ]
                ]
            ]);
    }
}