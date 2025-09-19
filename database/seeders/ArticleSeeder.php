<?php

namespace Database\Seeders;

use App\Models\Article;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Foundation\Testing\WithFaker;

class ArticleSeeder extends Seeder
{
    public function run(): void
    {
        Article::create([
            "title" => fake()->title(),
            "body"  => fake()->paragraph(),
        ]);

        Article::create([
            "title" => fake()->title(),
            "body"  => fake()->paragraph(),
        ]);
    }
}
