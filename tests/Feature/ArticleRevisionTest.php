<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Article;
use App\Models\ArticleRevision;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class ArticleRevisionTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Article $article;
    protected $revisions;

    // 👇 adjust this to match your routes if needed (e.g. "/api/v1/articles")
    protected string $basePath = '/api/articles';

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->article = Article::factory()->for($this->user)->create();

        $this->revisions = ArticleRevision::factory()
            ->count(3)
            ->for($this->article)
            ->create();
    }

    protected function authHeader(User $user): array
    {
        $token = auth()->login($user);
        return ['Authorization' => "Bearer {$token}"];
    }

    #[Test]
    public function it_returns_a_list_of_revisions_for_the_article()
    {
        $this->getJson("{$this->basePath}/{$this->article->id}/revisions", $this->authHeader($this->user))
            ->assertOk()
            ->assertJsonStructure([
                'revisions',
                'revisionsCount',
            ]);
    }

    #[Test]
    public function it_forbids_accessing_revisions_if_not_article_owner()
    {
        $otherUser = User::factory()->create();

        $this->getJson("{$this->basePath}/{$this->article->id}/revisions", $this->authHeader($otherUser))
            ->assertForbidden();
    }

    #[Test]
    public function it_shows_a_specific_revision_of_the_article()
    {
        $revision = $this->revisions->first();

        $this->getJson("{$this->basePath}/{$this->article->id}/revisions/{$revision->id}", $this->authHeader($this->user))
            ->assertOk()
            ->assertJsonStructure([
                'revision' => [
                    'id',
                    'title',
                    'description',
                    'body',
                ],
            ]);
    }

    #[Test]
    public function it_returns_404_if_revision_does_not_belong_to_article()
    {
        $otherArticle = Article::factory()->create();
        $revision = ArticleRevision::factory()->for($otherArticle)->create();

        $this->getJson("{$this->basePath}/{$this->article->id}/revisions/{$revision->id}", $this->authHeader($this->user))
            ->assertNotFound();
    }

    #[Test]
    public function it_reverts_article_to_a_previous_revision()
    {
        $revision = $this->revisions->first();

        $this->postJson("{$this->basePath}/{$this->article->id}/revisions/{$revision->id}/revert", [], $this->authHeader($this->user))
            ->assertOk()
            ->assertJson([
                'message' => 'Article reverted successfully',
                'article' => [
                    'id' => $this->article->id,
                ],
            ]);
    }

    #[Test]
    public function it_forbids_reverting_if_not_the_owner()
    {
        $otherUser = User::factory()->create();
        $revision = $this->revisions->first();

        $this->postJson("{$this->basePath}/{$this->article->id}/revisions/{$revision->id}/revert", [], $this->authHeader($otherUser))
            ->assertForbidden();
    }

    #[Test]
    public function it_returns_404_when_reverting_revision_not_belonging_to_article()
    {
        $otherArticle = Article::factory()->create(['user_id' => $this->user->id]);
        $revision = ArticleRevision::factory()->for($otherArticle)->create();

        $this->postJson("{$this->basePath}/{$this->article->id}/revisions/{$revision->id}/revert", [], $this->authHeader($this->user))
            ->assertNotFound();
    }
}
