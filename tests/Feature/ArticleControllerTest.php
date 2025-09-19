<?php

namespace Tests\Feature;

use App\Enums\CommentStatusEnum;
use App\Http\Middleware\UserRateLimiterMiddleware;
use App\Jobs\ModerateCommentJob;
use App\Models\Article;
use App\Models\Comment;
use App\Models\User;
use Database\Seeders\ArticleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Queue;
use Laravel\Sanctum\Sanctum;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class ArticleControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_get_articles(): void
    {
        $count = 4;

        Article::factory($count)->create();

        $response = $this->getJson('/api/articles/');

        $this->assertEquals(Article::count(),count($response->json("data.articles")));

        $article = $response->json("data.articles.0");

        $this->assertArrayHasKey("id",$article);
        $this->assertArrayHasKey("title",$article);
        $this->assertArrayHasKey("body",$article);

        $response->assertStatus(Response::HTTP_OK);
    }

    public function test_can_get_article(): void
    {
        $articleId = Article::factory()->create()->id;

        $response = $this->getJson("/api/articles/$articleId");

        $article = $response->json("data.article");

        $this->assertArrayHasKey("id",$article);
        $this->assertArrayHasKey("title",$article);
        $this->assertArrayHasKey("body",$article);

        $response->assertStatus(Response::HTTP_OK);
    }

    public function test_cannot_get_article_comment_without_login(): void
    {
        $articleId = Article::factory()->create()->id;

        $response = $this->getJson("/api/articles/$articleId/comments");

        $response->assertStatus(Response::HTTP_UNAUTHORIZED);
    }

    public function test_can_get_article_comment(): void
    {
        Sanctum::actingAs(
            User::factory()->create(),
            ['view-tasks']
        );

        $articleId = Article::factory()->create()->id;

        Comment::factory(4)->create([
            "article_id" => $articleId
        ]);

        $response = $this->getJson("/api/articles/$articleId/comments");

        $comment = $response->json("data.comments.0");

        $this->assertArrayHasKey("id", $comment);
        $this->assertArrayHasKey("content", $comment);
        $this->assertArrayHasKey("user_id", $comment);
        $this->assertArrayHasKey("article_id", $comment);
        $this->assertArrayHasKey("status", $comment);

        $response->assertStatus(Response::HTTP_OK);
    }

    public function test_can_get_article_comment_has_pagination(): void
    {
        Sanctum::actingAs(
            User::factory()->create(),
            ['view-tasks']
        );

        $perPage = 2;

        $articleId = Article::factory()->create()->id;

        Comment::factory(4)->create([
            "article_id" => $articleId
        ]);

        $response = $this->getJson("/api/articles/$articleId/comments?per_page=$perPage");

        $comments = $response->json("data.comments");

        $this->assertSame(count($comments), $perPage);

        $response = $this->getJson("/api/articles/$articleId/comments?per_page=$perPage&page=3");

        $comments = $response->json("data.comments");

        $this->assertSame(count($comments), 0);

        $response->assertStatus(Response::HTTP_OK);
    }

    public function test_can_store_article_comment_without_login(): void
    {
        $this->seed(ArticleSeeder::class);

        $articleId = Article::first()->id;

        $response = $this->postJson("/api/articles/$articleId/comments");

        $response->assertStatus(Response::HTTP_UNAUTHORIZED);
    }

    public function test_cannot_store_article_comment_without_data(): void
    {
        Sanctum::actingAs(
            User::factory()->create(),
            ['view-tasks']
        );

        $articleId = Article::factory()->create()->id;

        $response = $this->postJson("/api/articles/$articleId/comments");

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    public function test_can_store_article_comment(): void
    {
        Sanctum::actingAs(
            User::factory()->create(),
            ['view-tasks']
        );

        Queue::fake();

        $articleId = Article::factory()->create()->id;

        $response = $this->postJson("/api/articles/$articleId/comments",[
            "content" => fake()->text()
        ]);

        $response->assertStatus(Response::HTTP_ACCEPTED);

        $commentId = $response->json("data.comment.id");

        $comment = Comment::find($commentId);

        $this->assertSame($comment->status, CommentStatusEnum::PENDING->value);

        Queue::assertPushed(ModerateCommentJob::class);
    }

    public function test_can_store_article_comment_endpoint_has_rate_limits(): void
    {
        Sanctum::actingAs(
            User::factory()->create(),
            ['view-tasks']
        );

        Queue::fake();

        $articleId = Article::factory()->create()->id;

        for($i = 0 ; $i < 10 ; $i++){
            $response = $this->postJson("/api/articles/$articleId/comments",[
                "content" => fake()->text()
            ]);

            $response
                ->assertStatus(Response::HTTP_ACCEPTED);
        }

        $response = $this->postJson("/api/articles/$articleId/comments",[
            "content" => fake()->text()
        ]);

        $response
            ->assertStatus(Response::HTTP_TOO_MANY_REQUESTS);

    }

    public function test_can_store_article_comment_has_to_be_published(): void
    {
        Sanctum::actingAs(
            User::factory()->create(),
            ['view-tasks']
        );

        $articleId = Article::factory()->create()->id;

        $response = $this->postJson("/api/articles/$articleId/comments",[
            "content" => fake()->text()
        ]);

        $id = $response->json("data.comment.id");

        $response
            ->assertStatus(Response::HTTP_ACCEPTED);

        $comment = Comment::find($id);

        $this->assertSame($comment->status, CommentStatusEnum::PUBLISHED->value);
    }

    public function test_can_store_article_comment_has_to_be_rejected(): void
    {
        Sanctum::actingAs(
            User::factory()->create(),
            ['view-tasks']
        );

        $articleId = Article::factory()->create()->id;

        $response = $this->postJson("/api/articles/$articleId/comments",[
            "content" => fake()->text()." ".  implode(",",\config("comments.bannedWords"))
        ]);

        $id = $response->json("data.comment.id");

        $response
            ->assertStatus(Response::HTTP_ACCEPTED);

        $comment = Comment::find($id);

        $this->assertSame($comment->status, CommentStatusEnum::REJECTED->value);
    }
}
