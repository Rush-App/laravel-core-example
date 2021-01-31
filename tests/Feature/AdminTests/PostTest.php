<?php

namespace Tests\Feature\AdminTests;

use App\Models\Post\Post;
use App\Models\Post\PostTranslation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use RushApp\Core\Models\Language;
use Tests\Feature\BaseAdminTest;

class PostTest extends BaseAdminTest
{
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
     * all posts tests by correct admin token
     *
     * @test
     * @return void
     */
    public function allPostsTestsByCorrectAdminToken()
    {
        $this->setAdminToken();
        $this->setAdminId();

        $this->getPosts();
        $this->getPost();
        $this->getPostInvalidId();
        $this->createPost();
        $this->updatePost();
        $this->updatePostInvalidId();
        $this->deletePost();
        $this->deletePostInvalidId();

        $this->deleteAdmin();
    }

    /**
     * @return int
     */
    public function createPostData(): int
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

        $posts = Post::where('post_id', $postId)->get();
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
            'Authorization' => 'Bearer '.$this->adminToken,
        ])->getJson('admin/posts');

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
            'Authorization' => 'Bearer '.$this->adminToken,
        ])->getJson('admin/post/'.$postId);

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
            'Authorization' => 'Bearer '.$this->adminToken,
        ])->getJson('/post/'.$postId);

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
            'Authorization' => 'Bearer '.$this->adminToken,
        ])->postJson('admin/post', $this->data);

        $response->assertStatus(200)->assertJsonFragment($this->data);

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
            'Authorization' => 'Bearer '.$this->adminToken,
        ])->putJson('admin/post/'.$postId, $data);

        $response->assertStatus(200)->assertJsonFragment($data);

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
                'Authorization' => 'Bearer '.$this->adminToken,
            ])->putJson('/post/'.$postId);

        $response->assertStatus(404);
    }

    /**
     * delete one post
     *
     * @return void
     */
    public function deletePost()
    {
        $postId = $this->createPostData();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$this->adminToken,
        ])->deleteJson('admin/post/'.$postId);

        $response->assertStatus(200);

        $this->withHeaders([
            'Authorization' => 'Bearer '.$this->adminToken,
        ])->deletePostData($postId);

        $post = Post::first();
        if (!empty($post)) {
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
            'Authorization' => 'Bearer '.$this->adminToken,
        ])->deleteJson('/post/'.$postId);

        $response->assertStatus(404);
    }
}
