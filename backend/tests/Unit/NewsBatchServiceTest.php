<?php

namespace Tests\Unit;

use App\Models\News;
use App\Services\AIService;
use App\Services\NewsBatchService;
use App\Services\SocialService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NewsBatchServiceTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Ensure that news records are converted to social post text via the AI service
     * and handed off to the social service.
     */
    public function test_generate_post_and_dispatch(): void
    {
        $news = new News([
            'title' => 'Tokyo 2026 Olympics Update',
            'research_prompt' => '最新の進捗を簡潔にまとめて',
        ]);

        $aiMock = $this->createMock(AIService::class);
        $aiMock->expects($this->once())
            ->method('createPostContent')
            ->with($news->title, $news->research_prompt)
            ->willReturn('AI-generated summary');

        $socialMock = $this->createMock(SocialService::class);
        $socialMock->expects($this->once())
            ->method('postToX')
            ->with('AI-generated summary');

        $batch = new NewsBatchService($aiMock, $socialMock);
        $text = $batch->generatePostText($news);

        $this->assertSame('AI-generated summary', $text);
    }

    /**
     * Full end-to-end processDueNews using real model persistence.
     */
    public function test_process_due_news_updates_records_and_posts(): void
    {
        $news = News::factory()->create([
            'title' => 'Test News',
            'research_prompt' => '研究用プロンプト',
            'post_interval' => '1month',
        ]);

        $aiMock = $this->createMock(AIService::class);
        $aiMock->method('createPostContent')->willReturn('generated');
        $socialMock = $this->createMock(SocialService::class);
        $socialMock->expects($this->once())->method('postToX')->with('generated');

        $batch = new NewsBatchService($aiMock, $socialMock);

        $batch->processDueNews();

        $this->assertNotNull($news->fresh()->last_posted_at);
    }

    /**
     * Items that have been posted recently should not be posted again.
     */
    public function test_already_posted_not_due(): void
    {
        $news = News::factory()->create([
            'title' => 'Old News',
            'research_prompt' => '放置された',
            'post_interval' => '1month',
            'last_posted_at' => now()->subDays(15),
        ]);

        $aiMock = $this->createMock(AIService::class);
        $aiMock->expects($this->never())->method('createPostContent');
        $socialMock = $this->createMock(SocialService::class);
        $socialMock->expects($this->never())->method('postToX');

        $batch = new NewsBatchService($aiMock, $socialMock);
        $batch->processDueNews();

        $this->assertEquals($news->last_posted_at, $news->fresh()->last_posted_at);
    }
}
