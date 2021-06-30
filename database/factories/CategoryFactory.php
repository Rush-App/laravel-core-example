<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\CategoryTranslation;
use Illuminate\Database\Eloquent\Factories\Factory;
use RushApp\Core\Models\Language;

class CategoryFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Category::class;

    public function configure()
    {
        return $this->afterCreating(function (Category $category) {
            $languages = Language::all();

            foreach ($languages as $language) {
                CategoryTranslation::factory()->create([
                    'language_id' => $language->id,
                    'category_id' => $category->id,
                ]);
            }
        });
    }

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'status' => $this->faker->sentence,
        ];
    }
}
