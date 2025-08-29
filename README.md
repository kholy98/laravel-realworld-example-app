### Laravel implementation of RealWorld app

This Laravel app is part of the [RealWorld](https://github.com/gothinkster/realworld) project and implementation of the [Laravel best practices](https://github.com/alexeymezenin/laravel-best-practices).

You might also check [Ruby on Rails version](https://github.com/alexeymezenin/ruby-on-rails-realworld-example-app) of this app.

See how the exact same Medium.com clone (called [Conduit](https://demo.realworld.io)) is built using different [frontends](https://codebase.show/projects/realworld?category=frontend) and [backends](https://codebase.show/projects/realworld?category=backend). Yes, you can mix and match them, because **they all adhere to the same [API spec](https://gothinkster.github.io/realworld/docs/specs/backend-specs/introduction)**

### How to run the API

Make sure you have PHP and Composer installed globally on your computer.

Clone the repo and enter the project folder

```
git clone https://github.com/alexeymezenin/laravel-realworld-example-app.git
cd laravel-realworld-example-app
```

Install the app

```
composer install
cp .env.example .env
```

Run the web server

```
php artisan serve
```

That's it. Now you can use the api, i.e.

```
http://127.0.0.1:8000/api/articles
```


# Article Revisions API Documentation

## Overview

The Article Revisions API provides a comprehensive version control system for articles, allowing authors to access, view, and restore previous versions of their content. All endpoints require authentication and enforce strict ownership validation.

---

##  API Endpoints

### 1. GET `/api/articles/{article}/revisions`

Retrieves all revision history for a specific article.

#### Authorization
-  **Authentication Required**: User must be logged in
-  **Ownership Validation**: User must be the article author

#### Implementation
```php
public function index(Article $article)
{
    if ($article->user_id !== auth()->id()) {
        abort(403, 'Forbidden');
    }

    return new ArticleRevisionCollection(
        $article->revisions()->with('user')->latest()->get()
    );
}
```

## 2. GET `/api/articles/{article}/revisions/{revision}`

Retrieves detailed information about a specific revision.

### Authorization
-  **Authentication Required**: User must be logged in
- 🔍 **Relationship Validation**: Revision must belong to the specified article

### Implementation
```php
public function show(Article $article, ArticleRevision $revision)
{
    if ($revision->article_id !== $article->id) {
        abort(404, 'Revision not found for this article.');
    }

    return new ArticleRevisionResource($revision->load('user'));
}
```

### 3. POST /api/articles/{article}/revisions/{revision}/revert

Restores an article to a previous revision state.

---

####  Authorization
- **Authentication Required**: User must be logged in
-  **Ownership Validation**: User must be the article author
-  **Relationship Validation**: Revision must belong to the specified article

---

####  Implementation (PHP)
```php
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
```
