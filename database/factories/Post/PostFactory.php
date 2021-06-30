<?php

namespace Database\Factories\Post;

use App\Models\Category;
use App\Models\Post\Post;
use App\Models\Post\PostTranslation;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use RushApp\Core\Models\Language;

class PostFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Post::class;

    public function configure()
    {
        return $this->afterCreating(function (Post $post) {
            $languages = Language::all();

            foreach ($languages as $language) {
                PostTranslation::factory()->create([
                    'language_id' => $language->id,
                    'post_id' => $post->id,
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
            'published' => true,
            'published_at' => now(),
            'user_id' => User::factory(),
            'category_id' => Category::factory(),
        ];
    }
}
