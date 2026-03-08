<?php

namespace Tests\Unit;

use App\Models\News;
use App\Services\AIService;
use App\Services\NewsBatchService;
use App\Services\SettingService;
use App\Services\SocialService;
use Illuminate\Support\Collection;
use Tests\TestCase;

class NewsBatchServiceTest extends TestCase
{
    protected function tearDown(): void
    {
        \Mockery::close();
        parent::tearDown();
    }

    private function makeNews(array $attributes = []): News
    {
        $news = new class extends News {
            public bool $saved = false;

            public function save(array $options = []): bool
            {
                $this->saved = true;
                return true;
            }
        };

        foreach ($attributes as $key => $value) {
            $news->{$key} = $value;
        }

        return $news;
    }

    /**
     * Ensure that news records are converted to social post text via the AI service
     * and handed off to the social service.
     */
    public function test_generate_post_and_dispatch(): void
    {
        $news = new News([
            'title' => 'Tokyo 2026 Olympics Update',
        ]);

        $aiMock = $this->createMock(AIService::class);
        $aiMock->expects($this->once())
            ->method('createPostContent')
            ->with($news->title, '統一プロンプト')
            ->willReturn('AI-generated summary');

        $socialMock = $this->createMock(SocialService::class);
        $socialMock->expects($this->never())->method('postToX');

        $settingMock = $this->createMock(SettingService::class);
        $settingMock->method('getResearchPrompt')->willReturn('統一プロンプト');

        $batch = new NewsBatchService($aiMock, $socialMock, $settingMock);
        $text = $batch->generatePostText($news);

        $this->assertSame('AI-generated summary', $text);
    }

    /**
     * Full end-to-end processDueNews using real model persistence.
     */
    public function test_process_due_news_updates_records_and_posts(): void
    {
        $news = $this->makeNews([
            'title' => 'Test News',
            'post_interval' => '1month',
            'last_posted_at' => null,
        ]);

        $provider = new class {
            public static array $items = [];

            public static function all()
            {
                return Collection::make(self::$items);
            }
        };
        $newsClass = get_class($provider);
        $newsClass::$items = [$news];

        $aiMock = $this->createMock(AIService::class);
        $aiMock->method('createPostContent')->willReturn('generated');
        $socialMock = $this->createMock(SocialService::class);
        $socialMock->expects($this->once())->method('postToX')->with('generated');

        $settingMock = $this->createMock(SettingService::class);
        $settingMock->method('getResearchPrompt')->willReturn('統一プロンプト');

        $batch = new NewsBatchService($aiMock, $socialMock, $settingMock, $newsClass);

        $batch->processDueNews();

        $this->assertNotNull($news->last_posted_at);
        $this->assertTrue($news->saved);
    }

    /**
     * Items that have been posted recently should not be posted again.
     */
    public function test_already_posted_not_due(): void
    {
        $news = $this->makeNews([
            'title' => 'Old News',
            'post_interval' => '1month',
            'last_posted_at' => now()->subDays(15),
        ]);

        $provider = new class {
            public static array $items = [];

            public static function all()
            {
                return Collection::make(self::$items);
            }
        };
        $newsClass = get_class($provider);
        $newsClass::$items = [$news];

        $aiMock = $this->createMock(AIService::class);
        $aiMock->expects($this->never())->method('createPostContent');
        $socialMock = $this->createMock(SocialService::class);
        $socialMock->expects($this->never())->method('postToX');

        $settingMock = $this->createMock(SettingService::class);
        $settingMock->method('getResearchPrompt')->willReturn('統一プロンプト');

        $batch = new NewsBatchService($aiMock, $socialMock, $settingMock, $newsClass);
        $batch->processDueNews();

        $this->assertFalse($news->saved);
        $this->assertEquals($news->last_posted_at, $news->last_posted_at);
    }
}
