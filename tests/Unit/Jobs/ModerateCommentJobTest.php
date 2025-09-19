<?php

namespace Tests\Unit\Jobs;

use App\Enums\CommentStatusEnum;
use App\Jobs\ModerateCommentJob;
use App\Models\Comment;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\Log;
use Mockery;
use Tests\TestCase;

class ModerateCommentJobTest extends TestCase
{
    use DatabaseMigrations;

    public function test_published_data_cannot_to_be_change(): void
    {
        $comment = Comment::factory()->create();

        $this->travel(5)->second();

        $job = new ModerateCommentJob($comment);
        $job->handle();

        $cm = Comment::find($comment->id);

        $this->assertSame((string) $cm->created_at,(string) $cm->updated_at);
    }

    public function test_pending_data_have_be_publihed(): void
    {
        $comment = Comment::factory()->pending()->create();
        $this->travel(5)->second();

        $job = new ModerateCommentJob($comment);
        $job->handle();

        $cm = Comment::find($comment->id);

        $this->assertNotSame((string) $cm->created_at,(string) $cm->updated_at);

        $this->assertTrue($cm->status == CommentStatusEnum::PUBLISHED->value);
    }

    public function test_pending_wrong_data_have_be_rejected(): void
    {
        $comment = Comment::factory()->pending()->create([
            "content" => \implode(" ",config("comments.bannedWords"))
        ]);

        $this->travel(5)->second();

        $job = new ModerateCommentJob($comment);
        $job->handle();

        $cm = Comment::find($comment->id);

        $this->assertNotSame((string) $cm->created_at,(string) $cm->updated_at);

        $this->assertTrue($cm->status == CommentStatusEnum::REJECTED->value);
    }
}
