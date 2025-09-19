<?php

namespace App\Http\Controllers\Api;

use App\Enums\CommentStatusEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCommentRequest;
use App\Http\Resources\ArticleResource;
use App\Jobs\ModerateCommentJob;
use App\Models\Article;
use App\Models\Comment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class ArticleController extends Controller
{
    public function index(): JsonResponse
    {
        return $this->success([
            "articles" => ArticleResource::collection(
                Article::get()
            ),
        ]);
    }

    public function show(Article $article): JsonResponse
    {
        return $this->success([
            "article" => $article,
        ]);
    }

    public function comments(Request $request): JsonResponse
    {
        $page = $request->page ?? 1;
        $perPage = $request->per_page ?? 10;
        $artcileId = $request->article;

        $comments = Cache::tags(["articles","article:{$request->article}"])
            ->remember("comments:article:{$request->article}:{$perPage}:{$page}", config("cache.ttl"), function () use ($artcileId, $page, $perPage) {
                return Comment::query()
                    ->where("article_id", $artcileId)
                    ->where("status", CommentStatusEnum::PUBLISHED->value)
                    ->orderBy("created_at", "desc")
                    ->skip(($page - 1) * $perPage)
                    ->limit($perPage)
                    ->get();
            });

        return $this->success([
            "comments" => $comments,
        ]);
    }

    public function storeComments(StoreCommentRequest $request): JsonResponse
    {
        $artcileId = $request->article;

        $comment = Comment::create([
            "content"       => $request->content,
            "article_id"    => $artcileId,
            "user_id"       => $request->user()->id,
            "status"        => CommentStatusEnum::PENDING->value
        ]);

        ModerateCommentJob::dispatch($comment);

        return $this->success([
            "comment" => $comment,
        ],httpCode: Response::HTTP_ACCEPTED);
    }
}
