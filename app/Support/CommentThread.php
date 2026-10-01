<?php

namespace App\Support;

use Illuminate\Support\Collection;

class CommentThread
{
    /**
     * Comments in reading order for a threaded list: each top-level comment, in
     * the order given, followed by its replies oldest first. Replies are one
     * level deep, so each one sits right under the comment it answers.
     */
    public static function of(Collection $comments): Collection
    {
        $replies = $comments->whereNotNull('parent_id')->sortBy('id')->groupBy('parent_id');

        return $comments->whereNull('parent_id')
            ->flatMap(fn ($comment) => [$comment, ...$replies->get($comment->id, [])])
            ->values();
    }
}
