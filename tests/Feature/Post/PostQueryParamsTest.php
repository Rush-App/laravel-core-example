<?php

namespace Tests\Feature\Post;

use App\Models\Post\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\BaseFeatureTest;

class PostQueryParamsTest extends BaseFeatureTest
{
    use RefreshDatabase;

    protected string $entity = 'posts';

    /** @test */
    public function indexLimitTest()
    {
        $this->signIn()->assignAllActionsForAdminUser();

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
    public function indexOffsetTest()
    {
        $this->signIn()->assignAllActionsForAdminUser();

        $posts = Post::factory()->count(20)->create();

        $limitData = [
            'offset' => 15,
        ];
        $response = $this->json('GET', $this->entity, $limitData);
        $response
            ->assertOk()
            ->assertJsonCount(5);
    }

    /** @test */
    public function indexPaginateTest()
    {
        $this->signIn()->assignAllActionsForAdminUser();

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
        $this->signIn()->assignAllActionsForAdminUser();

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
        $this->signIn()->assignAllActionsForAdminUser();

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
        $this->signIn()->assignAllActionsForAdminUser();

        $posts = Post::factory()->count(10)->create();

        $response = $this->json('GET', $this->entity, [
            'order_by_field' => 'post_id:desc',
        ]);


        $expectedPostIds = Post::query()->orderBy('id', 'desc')->pluck('id');
        $postIdsFromEndpoint = collect(json_decode($response->getContent(), true))->pluck('id');

        $this->assertEquals($expectedPostIds, $postIdsFromEndpoint);

        $response
            ->assertOk()
            ->assertJsonCount(10);
    }

    /** @test */
    public function indexWhereNullTest()
    {
        $this->signIn()->assignAllActionsForAdminUser();

        Post::factory()->count(10)->create();
        $post1 = Post::factory()->create();
        $post1->translations()->update(['title' => null]);
        $post2 = Post::factory()->create();
        $post2->translations()->update(['title' => null]);

        $response = $this->json('GET', $this->entity, [
            'where_null' => 'title',
        ]);

        $this->assertDatabaseCount($this->entity, 12);
        $response
            ->assertOk()
            ->assertJsonCount(2);
    }

    /** @test */
    public function indexWhereBetweenTest()
    {
        $this->signIn()->assignAllActionsForAdminUser();

        $todayPosts = Post::factory()->count(10)->create();
        $lastWeekPosts = Post::factory()->count(5)->create([
            'published_at' => now()->subDays(7)
        ]);

        $fromPostId = $todayPosts->last()->id;
        $toPostId = $lastWeekPosts[2]->id;
        $fromDate = now()->subDays(10)->format('Y-m-d');
        $toDate = now()->subDays(5)->format('Y-m-d');

        $response = $this->json('GET', $this->entity, [
            'where_between' => "post_id:$fromPostId,$toPostId|published_at:$fromDate,$toDate",
        ]);

        $this->assertDatabaseCount($this->entity, 15);
        $response
            ->assertOk()
            ->assertJsonCount(3);
    }

    /** @test */
    public function indexWhereInTest()
    {
        $this->signIn()->assignAllActionsForAdminUser();

        $posts = Post::factory()->count(20)->create();

        $postIds = $posts->pluck('id');
        $searchedPostIds = $postIds->slice(0, 2)->merge(
            $postIds->slice(-3, 3)
        )->implode(',');

        $searchedUserIds = User::query()->pluck('id')->slice(0, 3)->implode(',');

        $response = $this->json('GET', $this->entity, [
            'where_in' => "post_id:$searchedPostIds|user_id:$searchedUserIds",
        ]);

        $this->assertDatabaseCount($this->entity, 20);
        $response
            ->assertOk()
            ->assertJsonCount(2);
    }

    /** @test */
    public function indexWhereNotInTest()
    {
        $this->signIn()->assignAllActionsForAdminUser();

        $posts = Post::factory()->count(20)->create();

        $postIds = $posts->pluck('id');
        $searchedPostIds = $postIds->slice(3, 2)->merge(
            $postIds->slice(-3, 3)
        )->implode(',');

        $searchedUserIds = User::query()->pluck('id')->slice(10, 1)->implode(',');

        $response = $this->json('GET', $this->entity, [
            'where_not_in' => "post_id:$searchedPostIds|user_id:$searchedUserIds",
        ]);

        $this->assertDatabaseCount($this->entity, 20);
        $response
            ->assertOk()
            ->assertJsonCount(14);
    }

    /** @test */
    public function indexWhereTest()
    {
        $this->signIn()->assignAllActionsForAdminUser();

        $todayPosts = Post::factory()->count(20)->create();
        Post::factory()->count(2)->create([
            'published_at' => now()->subDays(2),
        ]);
        $post = Post::query()->first();

        $this->json('GET', $this->entity, [
            'title' => 'like|%'.substr($post->translations->first()->title, 3),
        ])->assertOk()->assertJsonCount(1);

        $this->json('GET', $this->entity, [
            'title' => $post->translations->first()->title,
        ])->assertOk()->assertJsonCount(1);

        $this->json('GET', $this->entity, [
            'post_id' => '<>|'.$todayPosts->first()->id,
        ])->assertOk()->assertJsonCount(21);

        $this->json('GET', $this->entity, [
            'published_at' => '<|'.now()->subDays(1)->format('Y-m-d H:i:s'),
        ])->assertOk()->assertJsonCount(2);

        $this->json('GET', $this->entity, [
            'published_at' => '>|'.now()->subDay()->format('Y-m-d H:i:s'),
        ])->assertOk()->assertJsonCount(20);

        $this->json('GET', $this->entity, [
            'post_id' => '<=|'.$todayPosts->slice(0, 5)->last()->id,
        ])->assertOk()->assertJsonCount(5);

        $this->json('GET', $this->entity, [
            'post_id' => '>=|'.$todayPosts->slice(0, 10)->last()->id,
        ])->assertOk()->assertJsonCount(13);
    }
}
