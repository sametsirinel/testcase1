<?php

namespace App\Jobs;

use App\Enums\ArticleStatusEnum;
use App\Enums\CommentStatusEnum;
use App\Models\Article;
use App\Models\Comment;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class ModerateCommentJob implements ShouldQueue
{
    use Queueable;

    public $tries = 3;

    public function __construct(public Comment $comment)
    {
    }

    public function backoff(): array
    {
        return [1, 5, 10];
    }

    public function handle(): void
    {
        $comment = $this->comment;

        if($comment->status != CommentStatusEnum::PENDING->value){
            return;
        }

        $content = $comment->content;

        $bannedWords = \config("comments.bannedWords");

        foreach($bannedWords as $bannedWord){
            if(preg_match("/".$this->generateRegexVariations($bannedWord)."/u", $content)){
                $this->setCommentStatus(CommentStatusEnum::REJECTED->value);
                Log::info("Rejected $bannedWord in comment $content");
                return;
            }
        }

        Log::info("Published comment $content");
        $this->setCommentStatus(CommentStatusEnum::PUBLISHED->value);
    }

    protected function setCommentStatus(string $status){

        $this->comment->status = $status;
        $this->comment->save();

        if($status == CommentStatusEnum::PUBLISHED->value){
            Cache::tags("article:{$this->comment->article_id}")->flush();
        }
    }

    protected function generateRegexVariations(string $word): string
    {
        $substitutions = [
            'a' => '4',
            'e' => '3',
            's' => '5',
            'o' => '0',
            'i' => '1',
            'g' => '9',
            'b' => '8'
        ];

        $vowels = ['a', 'e', 'ı', 'i', 'o', 'ö', 'u', 'ü'];

        $regexParts = [];

        $characters = mb_str_split($word, 1, 'UTF-8');

        foreach ($characters as $char) {
            $lowerChar = mb_strtolower($char, 'UTF-8');

            $charVariations = [];

            $charVariations[$lowerChar] = true;
            $charVariations[mb_strtoupper($char, 'UTF-8')] = true;

            if (array_key_exists($lowerChar, $substitutions)) {
                $charVariations[$substitutions[$lowerChar]] = true;
            }

            $uniqueChars = array_keys($charVariations);
            sort($uniqueChars);

            $currentPart = '[' . implode('', $uniqueChars) . ']';

            if (in_array($lowerChar, $vowels)) {
                $currentPart .= "?";
            }

            $regexParts[] = $currentPart;
        }

        return implode('', $regexParts);
    }
}
