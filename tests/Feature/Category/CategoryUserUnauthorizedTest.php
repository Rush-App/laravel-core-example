<?php

namespace Tests\Feature\Category;

use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\BaseFeatureTest;

class CategoryUserUnauthorizedTest extends BaseFeatureTest
{
    use RefreshDatabase;

    protected string $entity = 'categories';

    /**
     * @test
     */
    public function indexTest()
    {
        $this->signIn();

        Category::factory()->count(5)->create();

        $response = $this->getJson($this->entity);
        $response->assertStatus(403);
    }

    /**
     * @test
     */
    public function showTest()
    {
        $this->signIn();

        $category = Category::factory()->create();

        $response = $this->getJson($this->entity.'/'.$category->id);

        $response->assertStatus(403);
    }

    /**
     * @test
     */
    public function storeTest()
    {
        $this->signIn();

        $categoryData = Category::factory()->raw();

        $response = $this->postJson($this->entity, $categoryData);

        $response->assertStatus(403);
        $this->assertDatabaseCount($this->entity, 0);
    }

    /**
     * @test
     */
    public function updateTest()
    {
        $this->signIn();

        /** @var Category $category */
        $category = Category::factory()->create();
        $categoryData['status'] = 'Changed status';
        $response = $this->putJson($this->entity.'/'.$category->id, $categoryData);

        $response->assertStatus(403);
        $this->assertDatabaseHas($this->entity, ['status' => $category->status]);
    }

    /**
     * @test
     */
    public function destroyPost()
    {
        $this->signIn();

        $category = Category::factory()->create();

        $this->assertDatabaseCount($this->entity, 1);

        $response = $this->deleteJson($this->entity.'/'.$category->id);

        $response->assertStatus(403);
        $this->assertDatabaseCount($this->entity, 1);
    }
}
