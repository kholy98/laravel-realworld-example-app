<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\Tag;
use App\Models\User;
use App\Models\ArticleRevision;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ArticleSeeder extends Seeder
{
    public function run(): void
    {
        // Create a few users
        $users = User::factory()->count(3)->create();

        $user = User::create([
            'username' => 'kholy',
            'email'    => 'k@k.com',
            'password' => bcrypt('secret123'),
            'bio'      => 'Test user bio',
            'image'    => 'https://i.pravatar.cc/150?u=kholy'
        ]);


        $users->push($user);

        // Create tags
        $tags = Tag::factory()->count(5)->create();

        // Create articles for each user
        $users->each(function (User $user) use ($tags) {
            $articles = Article::factory()
                ->count(3)
                ->for($user)
                ->create();

            // Attach random tags
            $articles->each(function (Article $article) use ($tags, $user) {
                $article->tags()->attach(
                    $tags->random(rand(1, 3))->pluck('id')->toArray()
                );

            });
        });
    }
}
