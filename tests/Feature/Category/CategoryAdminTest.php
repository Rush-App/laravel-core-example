<?php

namespace Tests\Feature\Category;

use App\Models\Category;
use App\Models\CategoryTranslation;
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
        $this->signIn()->assignAllActionsForAdminUser();

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
        $this->signIn()->assignAllActionsForAdminUser();

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
        $this->signIn()->assignAllActionsForAdminUser();

        $categoryData = Category::factory()->raw();
        $categoryData['name'] = 'testNamee';

        $response = $this->postJson($this->entity, $categoryData);

        $response->assertOk()->assertJsonFragment($categoryData);

        $this->assertDatabaseCount($this->entity, 1);
    }

    /**
     * @test
     */
    public function updateTest()
    {
        $this->signIn()->assignAllActionsForAdminUser();

        $category = Category::factory()->create()->toArray();
        $category['category_id'] = $category['id'];
        $category['language_id'] = $this->currentLanguage->id;
        $category['name'] = 'testNamee';
        $category['status'] = 'staaatus';

        $postTranslation = CategoryTranslation::create($category);

        $categoryData = [
            'name' => 'Changed title',
            'status' => 'Changed status'
        ];
        $response = $this->putJson($this->entity.'/'.$category['id'], $categoryData);

        $response->assertOk()->assertJsonFragment($categoryData);
        $this->assertDatabaseHas('category_translations', ['name' => 'Changed title']);
        $this->assertDatabaseHas($this->entity, ['status' => 'Changed status']);
    }

    /**
     * @test
     */
    public function destroyPost()
    {
        $this->signIn()->assignAllActionsForAdminUser();

        $category = Category::factory()->create();

        $this->assertDatabaseCount($this->entity, 1);

        $response = $this->deleteJson($this->entity.'/'.$category->id);

        $response->assertOk();
        $this->assertDatabaseCount($this->entity, 0);
    }
}
