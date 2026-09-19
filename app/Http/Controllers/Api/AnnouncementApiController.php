<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Helpers\SscHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AnnouncementApiController extends Controller
{
    /**
     * Get list of announcements with optional category filter.
     */
    public function index(Request $request)
    {
        $category = $request->input('category');

        $query = Announcement::with([
            'author:id,fullname,email,role,position,profile_pic',
            'proposal:id,title,total_cost',
            'comments.user:id,fullname,profile_pic',
        ])->orderByDesc('created_at');

        if ($category && in_array($category, [Announcement::CATEGORY_GENERAL, Announcement::CATEGORY_LOST_ITEM])) {
            $query->where('category', $category);
        }

        $announcements = $query->paginate($request->input('per_page', 15));

        // Format image URLs
        $announcements->getCollection()->transform(function ($announcement) {
            return [
                'id'             => $announcement->id,
                'title'          => $announcement->title,
                'content'        => $announcement->content,
                'category'       => $announcement->category,
                'category_label' => $announcement->category_label,
                'image_url'      => $announcement->image_path ? SscHelper::getUploadUrl($announcement->image_path) : null,
                'created_at'     => $announcement->created_at?->toIso8601String(),
                'author'         => $announcement->author ? [
                    'id'        => $announcement->author->id,
                    'fullname'  => $announcement->author->fullname,
                    'position'  => $announcement->author->position,
                    'photo_url' => $announcement->author->photo_url,
                ] : null,
                'proposal'       => $announcement->proposal ? [
                    'id'         => $announcement->proposal->id,
                    'title'      => $announcement->proposal->title,
                    'total_cost' => $announcement->proposal->total_cost,
                ] : null,
                'comments_count' => $announcement->comments->count(),
                'comments'       => $announcement->comments->map(function ($c) {
                    return [
                        'id'         => $c->id,
                        'comment'    => $c->comment,
                        'created_at' => $c->created_at?->toIso8601String(),
                        'user'       => [
                            'id'        => $c->user?->id,
                            'fullname'  => $c->user?->fullname,
                            'photo_url' => $c->user?->photo_url,
                        ],
                    ];
                }),
            ];
        });

        return response()->json([
            'success' => true,
            'data'    => $announcements,
        ]);
    }

    /**
     * Get single announcement.
     */
    public function show($id)
    {
        $announcement = Announcement::with([
            'author:id,fullname,email,role,position,profile_pic',
            'proposal:id,title,total_cost',
            'comments.user:id,fullname,profile_pic',
        ])->find($id);

        if (! $announcement) {
            return response()->json([
                'success' => false,
                'message' => 'Announcement not found.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data'    => [
                'id'             => $announcement->id,
                'title'          => $announcement->title,
                'content'        => $announcement->content,
                'category'       => $announcement->category,
                'category_label' => $announcement->category_label,
                'image_url'      => $announcement->image_path ? SscHelper::getUploadUrl($announcement->image_path) : null,
                'created_at'     => $announcement->created_at?->toIso8601String(),
                'author'         => $announcement->author ? [
                    'id'        => $announcement->author->id,
                    'fullname'  => $announcement->author->fullname,
                    'position'  => $announcement->author->position,
                    'photo_url' => $announcement->author->photo_url,
                ] : null,
                'proposal'       => $announcement->proposal,
                'comments'       => $announcement->comments->map(function ($c) {
                    return [
                        'id'         => $c->id,
                        'comment'    => $c->comment,
                        'created_at' => $c->created_at?->toIso8601String(),
                        'user'       => [
                            'id'        => $c->user?->id,
                            'fullname'  => $c->user?->fullname,
                            'photo_url' => $c->user?->photo_url,
                        ],
                    ];
                }),
            ],
        ]);
    }

    /**
     * Add a comment to a Lost & Found announcement.
     */
    public function comment(Request $request, $id)
    {
        $announcement = Announcement::find($id);

        if (! $announcement) {
            return response()->json([
                'success' => false,
                'message' => 'Announcement not found.',
            ], 404);
        }

        if ($announcement->category !== Announcement::CATEGORY_LOST_ITEM) {
            return response()->json([
                'success' => false,
                'message' => 'Comments are only allowed on Lost & Found items.',
            ], 400);
        }

        $validator = Validator::make($request->all(), [
            'comment' => 'required|string|min:1|max:2000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error.',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $user = auth('api')->user();

        $comment = $announcement->comments()->create([
            'user_id' => $user->id,
            'comment' => $request->comment,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Comment added successfully.',
            'data'    => [
                'id'         => $comment->id,
                'comment'    => $comment->comment,
                'created_at' => now()->toIso8601String(),
                'user'       => [
                    'id'        => $user->id,
                    'fullname'  => $user->fullname,
                    'photo_url' => $user->photo_url,
                ],
            ],
        ], 201);
    }
}
