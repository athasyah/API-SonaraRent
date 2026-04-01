<?php

namespace App\Contracts\Repositories;

use App\Contracts\Interfaces\ActivityLogInterface;
use App\Models\ActivityLog;

class ActivityLogRepository extends BaseRepository implements ActivityLogInterface
{
    public function __construct(ActivityLog $user)
    {
        $this->model = $user;
    }

    public function get()
    {
        return $this->model->get();
    }

    public function show(mixed $id)
    {
        return $this->model->find($id);
    }

    public function store(array $data)
    {
        return $this->model->create($data);
    }

    public function update(mixed $id, array $data): mixed
    {
        $model = $this->show($id);
        $model->update($data);

        return $model->fresh();
    }

    public function delete(mixed $id)
    {
        return $this->show($id)->delete();
    }

    public function customPaginate(int $perPage = 10, int $page = 1, ?array $data): mixed
    {
        $query = $this->model->query()
            ->with(['user'])
            ->orderBy('updated_at', 'desc');

        if (!empty($data['action'])) {
            $query->where('action', $data['action']);
        }

        if (!empty($data['module'])) {
            $query->where('module', $data['module']);
        }

        if (!empty($data['role'])) {
            $query->whereHas('user', function ($q) use ($data) {
                $q->role($data['role']);
            });
        }

        if (!empty($data['date_from'])) {
            $query->whereDate('created_at', '>=', $data['date_from']);
        }

        if (!empty($data['date_to'])) {
            $query->whereDate('created_at', '<=', $data['date_to']);
        }

        if (!empty($data['search'])) {
            $query->where(function ($q) use ($data) {
                $q->where('description', 'like', '%' . $data['search'] . '%');
            });
        }

        return $query->paginate($perPage, ['*'], 'page', $page);
    }

    public function noPaginate(array $data): mixed
    {
        $query = $this->model->query()
            ->with(['user'])
            ->orderBy('updated_at', 'desc')
            ->with(['user'])
            ->get();
        return $query;
    }
}
