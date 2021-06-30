<?php

namespace Tests\Feature\Post;

use App\Models\Category;
use App\Models\Post\Post;
use App\Models\Post\PostTranslation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\BaseFeatureTest;

class PostUserTest extends BaseFeatureTest
{
    use RefreshDatabase;

    protected string $entity = 'posts';

    /**
     * @test
     */
    public function indexTest()
    {
        $this->signIn()->assignAllActionsForAuthenticatedUser($this->entity.'.index');

        $authenticatedUserPosts = Post::factory()->count(5)->create(['user_id' => Auth::id()]);
        $otherUserPosts = Post::factory()->count(2)->create();

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
        $this->signIn()->assignAllActionsForAuthenticatedUser($this->entity.'.show');

        $post = Post::factory()->create(['user_id' => Auth::id()]);

        $response = $this->getJson($this->entity.'/'.$post->id);

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
        $this->signIn()->assignAllActionsForAuthenticatedUser($this->entity.'.store');

        $categoryData = $this->getDefaultCategoryData();
        $postData = $this->getDefaultPostData();

        $category = Category::create($categoryData);
        $postData['category_id'] = $category->id;

        $response = $this->postJson($this->entity, $postData);
        unset($postData['language']);
        $response->assertOk()->assertJsonFragment($postData);
        $this->assertDatabaseCount($this->entity, 1);
        $this->assertDatabaseCount($this->getTranslateTable($this->entity), 1);
    }

    /**
     * @test
     */
    public function updateTest()
    {
        $this->signIn()->assignAllActionsForAuthenticatedUser($this->entity.'.update');

        $categoryData = $this->getDefaultCategoryData();
        $postData = $this->getDefaultPostData();

        $category = Category::create($categoryData);

        $postData['user_id'] = Auth::id();
        $postData['category_id'] = $category->id;
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

    private function getDefaultCategoryData(): array
    {
        return  [
            'status' => 'statuuus',
            'name' => "test name",
            'language_id' => $this->currentLanguage->id,
            'language' => $this->currentLanguage->name,
        ];
    }

    /**
     * @test
     */
    public function destroyPost()
    {
        $this->signIn()->assignAllActionsForAuthenticatedUser($this->entity.'.destroy');

        $post = Post::factory()->create(['user_id' => Auth::id()]);

        $this->assertDatabaseCount($this->entity, 1);

        $response = $this->deleteJson($this->entity.'/'.$post->id);

        $response->assertOk();
        $this->assertDatabaseCount($this->entity, 0);
    }
}
