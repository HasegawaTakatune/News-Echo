<?php

namespace App\Services;

use App\Models\News;

/**
 * Interface for AI providers used by the application.
 */
interface AIService
{
    /**
     * Generate a social post text based on the news title and research prompt.
     *
     * @param string $title       the title of the news item
     * @param string|null $prompt optional research prompt stored on the news record
     * @return string             generated post text
     */
    public function createPostContent(string $title, ?string $prompt = null): string;
}
