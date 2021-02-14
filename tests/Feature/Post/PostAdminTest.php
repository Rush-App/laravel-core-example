<?php

namespace Tests\Feature\Post;

use App\Models\Post\Post;
use App\Models\Post\PostTranslation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use RushApp\Core\Models\Language;
use Tests\Feature\BaseTest;

class PostAdminTest extends BaseTest
{
    use RefreshDatabase;

    protected string $entity = 'posts';

    /**
     * @test
     */
    public function indexTest()
    {
        $this->signIn()->assignAllActionsForAuthenticatedUser($this->entity);

        $postTranslations = PostTranslation::factory()->count(5)->create(['language_id' => Language::first()->id]);

        $response = $this->getJson($this->entity);
        $response
            ->assertStatus(200)
            ->assertJsonCount(5)
            ->assertJsonStructure([
                '*' => [
                    'id',
                    'title',
                    'description',
                    'post_id',
                    'language_id',
                    'published',
                    'user_id',
                    'created_at',
                    'updated_at',
                ]
            ]);
    }

    /**
     * @test
     */
    public function showTest()
    {
        $this->signIn()->assignAllActionsForAuthenticatedUser($this->entity);

        $postTranslation = PostTranslation::factory()->create(['language_id' => Language::first()->id]);

        $response = $this->getJson($this->entity.'/'.$postTranslation->post_id);

        $response
            ->assertStatus(200)
            ->assertJsonStructure([
                'id',
                'title',
                'description',
                'post_id',
                'language_id',
                'published',
                'user_id',
                'created_at',
                'updated_at',
            ]);
    }

    /**
     * @test
     */
    public function storeTest()
    {
        $this->signIn()->assignAllActionsForAuthenticatedUser($this->entity);

        $postData = $this->getDefaultPostData();

        $response = $this->postJson($this->entity, $postData);
        unset($postData['fill_language']);
        $response->assertOk()->assertJsonFragment($postData);
        $this->assertDatabaseCount($this->entity, 1);
        $this->assertDatabaseCount($this->getTranslateTable($this->entity), 1);
    }

    /**
     * @test
     */
    public function updateTest()
    {
        $this->signIn()->assignAllActionsForAuthenticatedUser($this->entity);

        $postData = $this->getDefaultPostData();

        $postData['user_id'] = Auth::id();
        $post = Post::create($postData);
        $postData['post_id'] = $post->id;
        $postTranslation = PostTranslation::create($postData);

        $postData['title'] = 'Changed title';
        $postData['published'] = false;

        $response = $this->putJson($this->entity.'/'.$post->id, $postData);
        unset($postData['fill_language']);
        $response->assertOk()->assertJsonFragment($postData);

        $this->assertDatabaseHas($this->getTranslateTable($this->entity), ['title' => $postData['title']]);
        $this->assertDatabaseHas($this->entity, ['published' => false]);
    }

    private function getDefaultPostData(): array
    {
        return  [
            'title' => "Test title",
            'description' => "test desc",
            'language_id' => Language::query()->first()->id,
            'published' => true,
            'fill_language' => 'en',
        ];
    }

    /**
     * @test
     */
    public function destroyPost()
    {
        $this->signIn()->assignAllActionsForAuthenticatedUser($this->entity);

        $postTranslation = PostTranslation::factory()->create(['language_id' => Language::first()->id]);
        $postTranslation->post->update(['user_id' => Auth::id()]);

        $this->assertDatabaseCount($this->entity, 1);

        $response = $this->deleteJson($this->entity.'/'.$postTranslation->post_id);

        $response->assertOk();
        $this->assertDatabaseCount($this->entity, 0);
    }

    /**
     * @test
     */
    public function destroyPostByOwner()
    {
        $this->signIn();

        $postTranslation = PostTranslation::factory()->create(['language_id' => Language::first()->id]);
        $postTranslation->post->update(['user_id' => Auth::id()]);

        $this->assertDatabaseCount($this->entity, 1);

        $response = $this->deleteJson($this->entity.'/'.$postTranslation->post_id);

        $response->assertOk();
        $this->assertDatabaseCount($this->entity, 0);
    }
}