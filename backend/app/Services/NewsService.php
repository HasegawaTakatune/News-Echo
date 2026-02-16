<?php

namespace App\Services;

use App\Models\News;
use App\Models\User;
use App\Repositories\NewsRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class NewsService
{
    public function __construct(
        private NewsRepository $repository
    ) {}

    public function getList(User $user, int $page = 1): LengthAwarePaginator
    {
        return $this->repository->paginateByUser($user, 100);
    }

    public function create(User $user, array $data): News
    {
        $data['user_id'] = $user->id;
        return $this->repository->create($data);
    }

    public function update(News $news, array $data): News
    {
        return $this->repository->update($news, $data);
    }

    public function delete(News $news): void
    {
        $this->repository->delete($news);
    }

    public function findByIdAndUser(int $id, User $user): ?News
    {
        return $this->repository->findByIdAndUser($id, $user);
    }
}
