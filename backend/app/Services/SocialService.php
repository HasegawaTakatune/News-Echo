<?php

namespace App\Services;

/**
 * Abstraction for posting messages to social networks.
 */
class SocialService
{
    /**
     * Post text to X/Twitter.
     *
     * In a real implementation this would perform API authentication and
     * send the message. For now it's a stub so that the batch service can
     * be tested without an external dependency.
     *
     * @param string $text
     * @return void
     */
    public function postToX(string $text): void
    {
        // no-op; real code would call Twitter/X API
    }
}
