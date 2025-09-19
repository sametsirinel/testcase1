<?php namespace App\Enums;

enum CommentStatusEnum: string
{
    case PENDING = "pending";
    case PUBLISHED = "published";
    case REJECTED = "rejected";
}
