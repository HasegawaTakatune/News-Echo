<?php

namespace App\Repositories;

use App\Models\News;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class NewsRepository
{
    public function paginateByUser(User $user, int $perPage = 100): LengthAwarePaginator
    {
        return News::where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->paginate($perPage);
    }

    public function findByIdAndUser(int $id, User $user): ?News
    {
        return News::where('id', $id)->where('user_id', $user->id)->first();
    }

    public function create(array $data): News
    {
        return News::create($data);
    }

    public function update(News $news, array $data): News
    {
        $news->update($data);
        return $news;
    }

    public function delete(News $news): void
    {
        $news->delete();
    }
}
