<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class PostFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Post::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition(): array
    {
        // Crea una fecha aleatoria entre 1 y 20 días atrás
        $created_at = now()->subDays(rand(1, 20));
        $title = $this->faker->text(60);
        return [
            "user_id" => User::all()->random(1)->first()->id, // Id de un usuario aleatoriamente
            "category_id" => Category::all()->random(1)->first()->id, // Id de una categoria aleatoriamente
            "title" => $title,
            "content" => $this->faker->text(400),
            "likes" => rand(1, 20),
            "dislikes" => rand(1, 20),
            "created_at" => $created_at,
        ];
    }
}
