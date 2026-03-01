<?php

namespace App\Services;

use Illuminate\Http\Client\Factory as HttpFactory;

/**
 * A simple implementation of AIService that calls Google Gemini 3 Flash.
 *
 * NOTE: this is a stubbed example; in reality you would configure the
 * endpoint and credentials and parse the response appropriately.
 *
 * For the purposes of unit testing we will mock this service instead of
 * performing a real network request.
 */
class GeminiAIService implements AIService
{
    public function __construct(private HttpFactory $http)
    {
    }

    public function createPostContent(string $title, ?string $prompt = null): string
    {
        // build a prompt for Gemini
        $fullPrompt = "Generate an X post summarizing the news titled '{title}'";
        if ($prompt) {
            $fullPrompt .= " using the following research instructions: {$prompt}";
        }

        // example API call (pseudo-code)
        $response = $this->http->post('https://api.gemini.example.com/v1/flash', [
            'model' => 'gemini-3-flash',
            'input' => $fullPrompt,
            'max_output_tokens' => 60,
        ]);

        // this would normally inspect $response->json() for the generated text
        // to keep it simple we pretend the API returns {"output": "..."}
        $data = $response->json();
        return $data['output'] ?? "[unable to generate post]";
    }
}
