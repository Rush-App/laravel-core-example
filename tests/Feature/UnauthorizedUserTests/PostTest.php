<?php

namespace Tests\Feature\UnauthorizedUserTests;

use Tests\TestCase;

class PostTest extends TestCase
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
     * @return int
     */
    public function createPostData(): int
    {
        $post = new Post();
        $post->fill($this->data);

        return $post->id;
    }

    public function deletePostData($postId): void
    {
        Post::find($postId)->delete();
    }

    /**
     * get all posts
     *
     * @test
     * @return void
     */
    public function getPosts()
    {
        $postId = $this->createPostData();

        $response = $this->getJson('/posts');

        $response->assertStatus(200)->assertJsonStructure([
            '*' => $this->responseGetData
        ]);

        $this->deletePostData($postId);
    }

    /**
     * get one post
     *
     * @test
     * @return void
     */
    public function getPost()
    {
        $postId = $this->createPostData();

        $response = $this->getJson('/post/'.$postId);

        $response->assertStatus(200)->assertJsonStructure($this->responseGetData);

        $this->deletePostData($postId);
    }

    /**
     * get invalid one post
     *
     * @test
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
     * @test
     * @return void
     */
    public function createPost()
    {
        $response = $this->postJson('/post', $this->data);

        $response->assertStatus(401);

        $post = Post::first();
        if (empty($post)) {
            $this->fail('FAIL - post has been saved');
        }
    }

    /**
     * update one post
     *
     * @test
     * @return void
     */
    public function updatePost()
    {
        $postId = $this->createPostData();

        $response = $this->putJson('/post/'.$postId, $this->data);

        $response->assertStatus(401);

        $this->deletePostData($postId);
    }

    /**
     * update invalid one post
     *
     * @test
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
     * @test
     * @return void
     */
    public function deletePost()
    {
        $postId = $this->createPostData();

        $response = $this->deleteJson('/post/'.$postId);

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
     * @test
     * @return void
     */
    public function deletePostInvalidId()
    {
        $postId = 999999999999;

        $response = $this->deleteJson('/post/'.$postId);

        $response->assertStatus(404);
    }
}
