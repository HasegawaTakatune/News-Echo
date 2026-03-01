<?php

namespace Database\Factories;

use App\Models\News;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class NewsFactory extends Factory
{
    protected $model = News::class;

    public function definition()
    {
        $intervals = ['1month', '3months', '6months', '1year'];
        return [
            'user_id' => User::factory(),
            'title' => $this->faker->sentence,
            'post_interval' => $this->faker->randomElement($intervals),
            'research_prompt' => $this->faker->paragraph,
        ];
    }
}
