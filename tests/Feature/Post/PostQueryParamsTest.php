<?php

namespace Tests\Feature\Post;

use App\Models\Post\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\BaseFeatureTest;

class PostQueryParamsTest extends BaseFeatureTest
{
    use RefreshDatabase;

    protected string $entity = 'posts';

    /** @test */
    public function indexLimitTest()
    {
        $this->signIn()->assignAllActionsForAdminUser($this->entity);

        $posts = Post::factory()->count(20)->create();

        $limitData = [
            'limit' => 10,
        ];
        $response = $this->json('GET', $this->entity, $limitData);
        $response
            ->assertOk()
            ->assertJsonCount(10);
    }

    /** @test */
    public function indexPaginateTest()
    {
        $this->signIn()->assignAllActionsForAdminUser($this->entity);

        $posts = Post::factory()->count(20)->create();

        $data = [
            'paginate' => 5,
            'page' => 2,
        ];

        $response = $this->json('GET', $this->entity, $data);

        $expectedPostIds = Post::query()->limit(5)->offset(5)->pluck('id');
        $postIdsFromEndpoint = collect(json_decode($response->getContent(), true)['data'])->pluck('id');

        $response->assertOk();
        $this->assertEquals($expectedPostIds, $postIdsFromEndpoint);
    }

    /** @test */
    public function indexSelectedFieldsTest()
    {
        $this->signIn()->assignAllActionsForAdminUser($this->entity);

        $posts = Post::factory()->count(10)->create();

        $response = $this->json('GET', $this->entity, [
            'selected_fields' => 'id,title',
        ]);

        $response
            ->assertOk()
            ->assertJsonCount(10)
            ->assertJsonStructure([
                '*' => [
                    'id',
                    'title'
                ],
            ]);
    }

    /** @test */
    public function indexWhereNotNullTest()
    {
        $this->signIn()->assignAllActionsForAdminUser($this->entity);

        $posts = Post::factory()->count(10)->create();
        Post::create(Post::factory()->raw());

        $response = $this->json('GET', $this->entity, [
            'where_not_null' => 'title',
        ]);

        $this->assertDatabaseCount($this->entity, 11);
        $response
            ->assertOk()
            ->assertJsonCount(10);
    }

    /** @test */
    public function indexOrderByTest()
    {
        $this->signIn()->assignAllActionsForAdminUser($this->entity);

        $posts = Post::factory()->count(10)->create();

        $response = $this->json('GET', $this->entity, [
            'order_by_field' => 'posts.id:desc',
        ]);


        $expectedPostIds = Post::query()->orderBy('id', 'desc')->pluck('id');
        $postIdsFromEndpoint = collect(json_decode($response->getContent(), true))->pluck('id');

        $this->assertEquals($expectedPostIds, $postIdsFromEndpoint);

        $response
            ->assertOk()
            ->assertJsonCount(10);
    }
}