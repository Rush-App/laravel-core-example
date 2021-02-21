<?php

namespace Tests\Feature\Category;

use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\BaseFeatureTest;

class CategoryAdminTest extends BaseFeatureTest
{
    use RefreshDatabase;

    protected string $entity = 'categories';

    /**
     * @test
     */
    public function indexTest()
    {
        $this->signIn()->assignAllActionsForAdminUser($this->entity);

        $categories = Category::factory()->count(5)->create();

        $response = $this->getJson($this->entity);
        $response
            ->assertStatus(200)
            ->assertJsonCount(5)
            ->assertJsonStructure([
                '*' => [
                    'id',
                    'name',
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
        $this->signIn()->assignAllActionsForAdminUser($this->entity);

        $category = Category::factory()->create();

        $response = $this->getJson($this->entity.'/'.$category->id);

        $response
            ->assertStatus(200)
            ->assertJsonStructure([
                'id',
                'name',
                'created_at',
                'updated_at',
            ]);
    }

    /**
     * @test
     */
    public function storeTest()
    {
        $this->signIn()->assignAllActionsForAdminUser($this->entity);

        $categoryData = Category::factory()->raw();

        $response = $this->postJson($this->entity, $categoryData);

        $response->assertOk()->assertJsonFragment($categoryData);

        $this->assertDatabaseCount($this->entity, 1);
    }

    /**
     * @test
     */
    public function updateTest()
    {
        $this->signIn()->assignAllActionsForAdminUser($this->entity);

        $category = Category::factory()->create();
        $categoryData['name'] = 'Changed title';
        $response = $this->putJson($this->entity.'/'.$category->id, $categoryData);

        $response->assertOk()->assertJsonFragment($categoryData);
        $this->assertDatabaseHas($this->entity, ['name' => 'Changed title']);
    }

    /**
     * @test
     */
    public function destroyPost()
    {
        $this->signIn()->assignAllActionsForAdminUser($this->entity);

        $category = Category::factory()->create();

        $this->assertDatabaseCount($this->entity, 1);

        $response = $this->deleteJson($this->entity.'/'.$category->id);

        $response->assertOk();
        $this->assertDatabaseCount($this->entity, 0);
    }
}