<?php

namespace Tests\Feature\AdminTests;

use Tests\Feature\BaseAdminTest;

class UnuatorizedAdminPostTest extends BaseAdminTest
{
    /**
     * @var array
     */
    public array $data = [
        'language_id' => 1,
    ];

    /**
     * @var array
     */
    public array $responseGetData = [
        'language_id' => 1,
    ];

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
        $post = new Post();
        $post->fill($this->data);

        $data = $this->data;
        $data['post_id'] = $post->id;

        $postTranslation = new PostTranslation();
        $postTranslation->fill($data);

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

        $response = $this->getJson('admin/posts');

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

        $response = $this->getJson('admin/post/'.$postId);

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

        $response = $this->getJson('/post/'.$postId);

        $response->assertStatus(404);
    }

    /**
     * create one post
     *
     * @return void
     */
    public function createPost()
    {
        $response = $this->postJson('admin/post', $this->data);

        $response->assertStatus(401);

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

        $response = $this->putJson('admin/post/'.$postId, $data);

        $response->assertStatus(401);

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

        $response = $this->putJson('/post/'.$postId);

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

        $response = $this->deleteJson('admin/post/'.$postId);

        $response->assertStatus(401);

        $this->deletePostData($postId);

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

        $response = $this->deleteJson('/post/'.$postId);

        $response->assertStatus(404);
    }
}