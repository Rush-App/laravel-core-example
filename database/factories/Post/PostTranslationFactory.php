<?php

namespace Database\Factories\Post;

use App\Models\Post\Post;
use App\Models\Post\PostTranslation;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use RushApp\Core\Models\Language;

class PostTranslationFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = PostTranslation::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $languageId = Language::query()->first() ?: new Language(['name' => 'en']);
        return [
            'title' => $this->faker->title,
            'description' => $this->faker->title,
            'post_id' => Post::factory(),
            'language_id' => $languageId,
        ];
    }
}
