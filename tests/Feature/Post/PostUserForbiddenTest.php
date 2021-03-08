<?php

namespace Tests\Feature\Post;

use App\Models\Post\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\BaseFeatureTest;

class PostUserForbiddenTest extends BaseFeatureTest
{
    use RefreshDatabase;

    protected string $entity = 'posts';

    /**
     * @test
     */
    public function indexTest()
    {
        $this->signIn()->assignAllActionsForAuthenticatedUser($this->entity, 'index');

        $otherUserPosts = Post::factory()->count(5)->create();

        $response = $this->getJson($this->entity);
        $response
            ->assertStatus(200)
            ->assertJsonCount(0);
    }

    /**
     * @test
     */
    public function showTest()
    {
        $this->signIn();

        $post = Post::factory()->create();

        $response = $this->getJson($this->entity.'/'.$post->id);

        $response->assertStatus(403);
    }

    /**
     * @test
     */
    public function showOtherUserPostTest()
    {
        $this->signIn()->assignAllActionsForAuthenticatedUser($this->entity, 'show', true);

        $post = Post::factory()->create();

        $response = $this->getJson($this->entity.'/'.$post->id);

        $response->assertStatus(403);
    }

    /**
     * @test
     */
    public function storeTest()
    {
        $this->signIn();

        $postData = $this->getDefaultPostData();

        $response = $this->postJson($this->entity, $postData);
        unset($postData['language']);

        $response->assertStatus(403);

        $this->assertDatabaseCount($this->entity, 0);
        $this->assertDatabaseCount($this->getTranslateTable($this->entity), 0);
    }

    /**
     * @test
     */
    public function updateTest()
    {
        $this->signIn();

        /** @var Post $post */
        $post = Post::factory()->create();

        $postData['title'] = 'Changed title';
        $postData['published'] = false;

        $response = $this->putJson($this->entity.'/'.$post->id, $postData);
        unset($postData['language']);

        $response->assertStatus(403);

        $this->assertDatabaseHas($this->getTranslateTable($this->entity), ['title' => $post->translations->first()->title]);
        $this->assertDatabaseHas($this->entity, ['published' => $post->published]);
    }

    /**
     * @test
     */
    public function updateOtherUserPostTest()
    {
        $this->signIn()->assignAllActionsForAuthenticatedUser($this->entity, 'update', true);

        /** @var Post $post */
        $post = Post::factory()->create();

        $postData['title'] = 'Changed title';
        $postData['published'] = false;

        $response = $this->putJson($this->entity.'/'.$post->id, $postData);
        unset($postData['language']);

        $response->assertStatus(403);

        $this->assertDatabaseHas($this->getTranslateTable($this->entity), ['title' => $post->translations->first()->title]);
        $this->assertDatabaseHas($this->entity, ['published' => $post->published]);
    }

    private function getDefaultPostData(): array
    {
        return  [
            'title' => "Test title",
            'description' => "test desc",
            'language_id' => $this->currentLanguage->id,
            'published' => true,
            'language' => $this->currentLanguage->name,
        ];
    }

    /**
     * @test
     */
    public function destroyPost()
    {
        $this->signIn();

        $post = Post::factory()->create();

        $this->assertDatabaseCount($this->entity, 1);

        $response = $this->deleteJson($this->entity.'/'.$post->id);

        $response->assertStatus(403);
        $this->assertDatabaseCount($this->entity, 1);
    }

    /**
     * @test
     */
    public function destroyOtherUserPost()
    {
        $this->signIn()->assignAllActionsForAuthenticatedUser($this->entity, 'destroy', true);

        $post = Post::factory()->create();

        $this->assertDatabaseCount($this->entity, 1);

        $response = $this->deleteJson($this->entity.'/'.$post->id);

        $response->assertStatus(403);
        $this->assertDatabaseCount($this->entity, 1);
    }
}