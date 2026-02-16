<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\News;
use App\Services\NewsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NewsController extends Controller
{
    public function __construct(
        private NewsService $newsService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $paginator = $this->newsService->getList($request->user());
        return response()->json($paginator);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'post_interval' => 'required|in:1month,3months,6months,1year',
            'user_id' => 'sometimes|exists:users,id',
        ]);

        $news = $this->newsService->create($request->user(), $validated);
        return response()->json($news, 201);
    }

    public function show(Request $request, News $news): JsonResponse
    {
        $news = $this->newsService->findByIdAndUser($news->id, $request->user());
        if (!$news) {
            return response()->json(['message' => '見つかりません。'], 404);
        }
        return response()->json($news);
    }

    public function update(Request $request, News $news): JsonResponse
    {
        $news = $this->newsService->findByIdAndUser($news->id, $request->user());
        if (!$news) {
            return response()->json(['message' => '見つかりません。'], 404);
        }

        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'post_interval' => 'sometimes|in:1month,3months,6months,1year',
            'research_prompt' => 'nullable|string|max:1000',
        ]);

        $news = $this->newsService->update($news, $validated);
        return response()->json($news);
    }

    public function destroy(Request $request, News $news): JsonResponse
    {
        $news = $this->newsService->findByIdAndUser($news->id, $request->user());
        if (!$news) {
            return response()->json(['message' => '見つかりません。'], 404);
        }

        $this->newsService->delete($news);
        return response()->json(null, 204);
    }
}
