<?php

namespace Tests\Feature\Post;

use App\Models\Category;
use App\Models\Post\Post;
use App\Models\Post\PostTranslation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\BaseFeatureTest;

class PostAdminTest extends BaseFeatureTest
{
    use RefreshDatabase;

    protected string $entity = 'posts';

    /**
     * @test
     */
    public function indexTest()
    {
        $this->signIn()->assignAllActionsForAdminUser();

        $posts = Post::factory()->count(5)->create();

        $response = $this->getJson($this->entity);
        $response
            ->assertStatus(200)
            ->assertJsonCount(5)
            ->assertJsonStructure([
                '*' => [
                    'id',
                    'title',
                    'description',
                    'published',
                    'title',
                    'description',
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
        $this->signIn()->assignAllActionsForAdminUser();

        $post = Post::factory()->create();

        $response = $this->getJson($this->entity.'/'.$post->id);

        $response
            ->assertStatus(200)
            ->assertJsonStructure([
                'id',
                'title',
                'description',
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
        $this->signIn()->assignAllActionsForAdminUser();

        $postData = $this->getDefaultPostData();

        $response = $this->postJson($this->entity, $postData);
        unset($postData['language']);
        $response->assertOk()->assertJsonFragment($postData);
        $this->assertDatabaseCount($this->entity, 1);
        $this->assertDatabaseCount($this->getTranslateTable($this->entity), 1);
    }

    /**
     * @test
     */
    public function storeWithValidationErrorsTest()
    {
        $this->signIn()->assignAllActionsForAdminUser();

        $postData = $this->getDefaultPostData();
        $postData['title'] = 'te';

        $response = $this->postJson($this->entity, $postData);
        unset($postData['language']);
        $response->assertStatus(422)->assertJsonValidationErrors('title');
        $this->assertDatabaseCount($this->entity, 0);
        $this->assertDatabaseCount($this->getTranslateTable($this->entity), 0);
    }

    /**
     * @test
     */
    public function updateTest()
    {
        $this->signIn()->assignAllActionsForAdminUser();

        $postData = $this->getDefaultPostData();

        $postData['user_id'] = User::factory()->create()->id;
        $post = Post::create($postData);
        $postData['post_id'] = $post->id;
        $postTranslation = PostTranslation::create($postData);

        $postData['title'] = 'Changed title';
        $postData['published'] = false;

        $response = $this->putJson($this->entity.'/'.$post->id, $postData);
        unset($postData['language']);
        $response->assertOk()->assertJsonFragment($postData);

        $this->assertDatabaseHas($this->getTranslateTable($this->entity), ['title' => $postData['title']]);
        $this->assertDatabaseHas($this->entity, ['published' => false]);
    }

    /**
     * @test
     */
    public function updateWithValidationErrorsTest()
    {
        $this->signIn()->assignAllActionsForAdminUser();

        $postData = $this->getDefaultPostData();

        $postData['user_id'] = User::factory()->create()->id;
        $post = Post::create($postData);
        $postData['post_id'] = $post->id;
        $postTranslation = PostTranslation::create($postData);

        $postData['title'] = 'tes';
        $postData['description'] = 'des';
        $postData['published'] = false;

        $response = $this->putJson($this->entity.'/'.$post->id, $postData);
        unset($postData['language']);
        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors(['title', 'description']);
    }

    private function getDefaultPostData(): array
    {
        return  [
            'title' => "Test title",
            'description' => "test desc",
            'language_id' => $this->currentLanguage->id,
            'published' => true,
            'language' => $this->currentLanguage->name,
            'category_id' => Category::factory()->create()->id,
        ];
    }

    /**
     * @test
     */
    public function destroyPost()
    {
        $this->signIn()->assignAllActionsForAdminUser();

        $post = Post::factory()->create(['user_id' => Auth::id()]);

        $this->assertDatabaseCount($this->entity, 1);

        $response = $this->deleteJson($this->entity.'/'.$post->id);

        $response->assertOk();
        $this->assertDatabaseCount($this->entity, 0);
    }

    /**
     * @test
     */
    public function destroyPostByOwner()
    {
        $this->signIn()->assignAllActionsForAuthenticatedUser($this->entity, 'destroy', true);

        $post = Post::factory()->create(['user_id' => Auth::id()]);

        $this->assertDatabaseCount($this->entity, 1);

        $response = $this->deleteJson($this->entity.'/'.$post->id);

        $response->assertOk();
        $this->assertDatabaseCount($this->entity, 0);
    }
}