<?php

namespace Tests\OldFeature\AuthorizedUserTests;

use App\Models\Post\Post;
use App\Models\Post\PostTranslation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use RushApp\Core\Models\Language;
use Tests\OldFeature\BaseUserTest;

class PostTest extends BaseUserTest
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Language::query()->truncate();
        Post::query()->truncate();
        PostTranslation::query()->truncate();
        Language::create(['name' => 'en']);
    }

    /**
     * @var array
     */
    public array $data = [
        'title' => "Test title",
        'description' => "test desc",
        'language_id' => 1,
        'published' => true,
        'fill_language' => 'en',
    ];

    /**
     * @var array
     */
    public array $responseGetData = [
        'title',
        'description',
        'language_id',
        'published',
    ];

    /**
     * all posts tests by correct user token
     *
     * @test
     * @return void
     */
    public function allPostsTestsByCorrectUserToken()
    {
        User::query()->truncate();

        $this->setUserToken();
        $this->setUserId();
        $this->data['user_id'] = $this->userId;

        $this->getPosts();
        $this->getPost();
        $this->getPostInvalidId();
        $this->createPost();
        $this->updatePost();
        $this->updatePostInvalidId();
        $this->updateForeignPost();
        $this->deletePost();
        $this->deletePostInvalidId();
        $this->deleteForeignPost();

        $this->deleteUser();
    }

    /**
     * @param array $newData
     * @return int
     */
    public function createPostData(array $newData = []): int
    {
        $data = !empty($newData) ? $newData : $this->data;

        $post = Post::create($data);

        $data['post_id'] = $post->id;

        $postTranslation = PostTranslation::create($data);

        return $post->id;
    }

    public function deletePostData($postId): void
    {
        Post::find($postId)->delete();

        $posts = Post::where('id', $postId)->get();
        foreach ($posts as $post) {
            $post->delete();
        }
    }

    /**
     * get all posts
     *
     * @return void
     */
    public function getPosts()
    {
        $postId = $this->createPostData();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$this->userToken,
        ])->getJson('posts');

        $response->assertStatus(200)->assertJsonStructure([
            '*' => $this->responseGetData
        ]);

        $this->deletePostData($postId);
    }

    /**
     * get one post
     *
     * @return void
     */
    public function getPost()
    {
        $postId = $this->createPostData();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$this->userToken,
        ])->getJson('/posts/'.$postId);

        $response->assertStatus(200)->assertJsonStructure($this->responseGetData);

        $this->deletePostData($postId);
    }

    /**
     * get invalid one post
     *
     * @return void
     */
    public function getPostInvalidId()
    {
        $postId = 999999999999;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$this->userToken,
        ])->getJson('/posts/'.$postId);

        $response->assertStatus(404);
    }

    /**
     * create one post
     *
     * @return void
     */
    public function createPost()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$this->userToken,
        ])->postJson('post', $this->data);

        $res = $this->data;
        unset($res['fill_language']);

        $response->assertStatus(200)->assertJsonFragment($res);

        $post = Post::first();
        if (empty($post)) {
            $this->fail('FAIL - post has been saved');
        }
    }

    /**
     * update one post
     *
     * @return void
     */
    public function updatePost()
    {
        $postId = $this->createPostData();

        $data = $this->data;
        $data['someElem'] = 'data';

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$this->userToken,
        ])->putJson('post/'.$postId, $data);

        $res = $this->data;
        unset($res['fill_language']);

        $response->assertStatus(200)->assertJsonFragment($res);

        $this->deletePostData($postId);
    }

    /**
     * update invalid one post
     *
     * @return void
     */
    public function updatePostInvalidId()
    {
        $postId = 999999999999;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$this->userToken,
        ])->putJson('/posts/'.$postId);

        $response->assertStatus(404);
    }

    /**
     * update invalid one post
     *
     * @return void
     */
    public function updateForeignPost()
    {
        /** @var Post $post */
        $post = Post::factory()->create();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$this->userToken,
        ])->putJson('/posts/'.$post->id);
        $response->assertStatus(403);

        $this->deletePostData($post->id);
    }

    /**
     * delete one post
     *
     * @return void
     */
    public function deletePost()
    {
        PostTranslation::query()->truncate();
        $postId = $this->createPostData();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$this->userToken,
        ])->deleteJson('post/'.$postId);

        $response->assertStatus(200);

        $post = Post::find($postId);
        $postTranslation = PostTranslation::wherePostId($postId)->first();

        if ($post || $postTranslation) {
            $this->fail('FAIL - post hasnt been deleted');
        }
    }

    /**
     * delete invalid one post
     *
     * @return void
     */
    public function deletePostInvalidId()
    {
        $postId = 999999999999;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$this->userToken,
        ])->deleteJson('/posts/'.$postId);

        $response->assertStatus(404);
    }

    /**
     * delete invalid one post
     *
     * @return void
     */
    public function deleteForeignPost()
    {
        /** @var Post $post */
        $post = Post::factory()->create();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$this->userToken,
        ])->deleteJson('/posts/'.$post->id);

        $response->assertStatus(403);

        $this->deletePostData($post->id);
    }
}
