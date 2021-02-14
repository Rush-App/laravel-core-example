<?php

namespace Tests\OldFeature\UnauthorizedUserTests;

use Tests\TestCase;

class PostTest extends TestCase
{
    private string $entityName = 'posts';

    /**
     * get all posts
     *
     * @test
     * @return void
     */
    public function getPosts()
    {
        $response = $this->getJson('/'.$this->entityName);

        $response->assertStatus(401);
    }

    /**
     * get one post
     *
     * @test
     * @return void
     */
    public function getPost()
    {
        $response = $this->getJson('/'.$this->entityName.'/99999');

        $response->assertStatus(401);
    }

    /**
     * create one post
     *
     * @test
     * @return void
     */
    public function createPost()
    {
        $response = $this->postJson('/'.$this->entityName, ['test'=>'kek']);

        $response->assertStatus(401);
    }

    /**
     * update one post
     *
     * @test
     * @return void
     */
    public function updatePost()
    {
        $response = $this->putJson('/'.$this->entityName.'/99999', ['test'=>'kek']);

        $response->assertStatus(401);
    }

    /**
     * delete one post
     *
     * @test
     * @return void
     */
    public function deletePost()
    {
        $response = $this->deleteJson('/'.$this->entityName.'/9999');

        $response->assertStatus(401);
    }
}
