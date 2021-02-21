<?php

namespace Tests\Feature\Post;

use App\Models\Post\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\BaseFeatureTest;

class PostUserUnauthorizedTest extends BaseFeatureTest
{
    use RefreshDatabase;

    protected string $entity = 'posts';

    /**
     * @test
     */
    public function indexTest()
    {
        $posts = Post::factory()->count(5)->create();

        $response = $this->getJson($this->entity);
        $response->assertStatus(401);
    }

    /**
     * @test
     */
    public function showTest()
    {
        $post = Post::factory()->create();

        $response = $this->getJson($this->entity.'/'.$post->id);

        $response->assertStatus(401);
    }

    /**
     * @test
     */
    public function storeTest()
    {
        $postData = $this->getDefaultPostData();

        $response = $this->postJson($this->entity, $postData);
        unset($postData['fill_language']);

        $response->assertStatus(401);

        $this->assertDatabaseCount($this->entity, 0);
        $this->assertDatabaseCount($this->getTranslateTable($this->entity), 0);
    }

    /**
     * @test
     */
    public function updateTest()
    {
        /** @var Post $post */
        $post = Post::factory()->create();

        $postData['title'] = 'Changed title';
        $postData['published'] = false;

        $response = $this->putJson($this->entity.'/'.$post->id, $postData);
        unset($postData['fill_language']);

        $response->assertStatus(401);

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
            'fill_language' => $this->currentLanguage->name,
        ];
    }

    /**
     * @test
     */
    public function destroyPost()
    {
        $post = Post::factory()->create();

        $this->assertDatabaseCount($this->entity, 1);

        $response = $this->deleteJson($this->entity.'/'.$post->id);

        $response->assertStatus(401);
        $this->assertDatabaseCount($this->entity, 1);
    }
}