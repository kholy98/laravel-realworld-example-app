<?php

namespace App\Http\Controllers;

use App\Http\Resources\ArticleRevisionCollection;
use App\Http\Resources\ArticleRevisionResource;
use App\Models\Article;
use App\Models\ArticleRevision;
use Illuminate\Support\Facades\Auth;

class ArticleRevisionController extends Controller
{
    public function index(Article $article)
    {
        if ($article->user_id !== auth()->id()) {
            abort(403, 'Forbidden');
        }

        return new ArticleRevisionCollection(
            $article->revisions()->with('user')->latest()->get()
        );
    }

    public function show(Article $article, ArticleRevision $revision)
    {
        if ($revision->article_id !== $article->id) {
            abort(404, 'Revision not found for this article.');
        }

        return new ArticleRevisionResource($revision->load('user'));
    }

    public function revert(Article $article, ArticleRevision $revision)
    {

        if ($article->user_id !== auth()->id()) {
            abort(403, 'Forbidden');
        }

        if ($revision->article_id !== $article->id) {
            abort(404, 'Revision not found for this article.');
        }

        $article->update([
            'title'       => $revision->title,
            'description' => $revision->description,
            'body'        => $revision->body,
            'slug'        => $revision->slug,
        ]);

        return response()->json([
            'message' => 'Article reverted successfully',
            'article' => $article->fresh()->load('user', 'users', 'tags'),
        ]);
    }
}

