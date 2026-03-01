<?php

namespace App\Services;

use App\Models\News;
use Illuminate\Support\Carbon;

/**
 * Handles periodic processing of news items, including AI-powered
 * generation of social media posts and dispatching them.
 */
class NewsBatchService
{
    public function __construct(
        private AIService $aiService,
        private SocialService $socialService
    ) {
    }

    /**
     * Create the text for a social post from a news record.
     *
     * @param News $news
     * @return string
     */
    public function generatePostText(News $news): string
    {
        return $this->aiService->createPostContent(
            $news->title,
            $news->research_prompt
        );
    }

    /**
     * Scan for news items that should be posted today and send them to X.
     *
     * The example here is simplistic: every news item that has not been
     * posted in the last interval is considered due. A real implementation
     * would track last_posted_at and take post_interval into account.
     *
     * @return void
     */
    public function processDueNews(): void
    {
        $now = Carbon::now();
        $all = News::all();

        foreach ($all as $news) {
            if (!$this->isDue($news, $now)) {
                continue;
            }

            $text = $this->generatePostText($news);
            $this->socialService->postToX($text);
            $news->last_posted_at = $now;
            $news->save();
        }
    }

    private function isDue(News $news, Carbon $now): bool
    {
        if (is_null($news->last_posted_at)) {
            return true;
        }

        $map = [
            '1month' => 1,
            '3months' => 3,
            '6months' => 6,
            '1year' => 12,
        ];

        $months = $map[$news->post_interval] ?? 1;
        return $news->last_posted_at->copy()->addMonths($months)->lte($now);
    }
}
