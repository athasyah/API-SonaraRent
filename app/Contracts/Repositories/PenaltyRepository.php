<?php

namespace App\Contracts\Repositories;

use App\Contracts\Interfaces\PenaltyInterface;
use App\Models\Penalty;

class PenaltyRepository extends BaseRepository implements PenaltyInterface
{
    public function __construct(Penalty $user)
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
            ->orderBy('created_at', 'desc')
            ->with(['rental.customer', 'condition.instrument']);

        if (!empty($data['search'])) {
            $query->whereHas('rental.customer', function ($q) use ($data) {
                $q->where('name', 'like', '%' . $data['search'] . '%');
            });
        }

        if (!empty($data['date_from'])) {
            $query->whereDate('created_at', '>=', $data['date_from']);
        }

        if (!empty($data['date_to'])) {
            $query->whereDate('created_at', '<=', $data['date_to']);
        }

        return $query->paginate($perPage, ['*'], 'page', $page);
    }

    public function noPaginate(array $data): mixed
    {
        $query = $this->model->query()
            ->orderBy('created_at', 'desc')
            ->with(['rental.customer', 'condition.instrument']);

        if (!empty($data['search'])) {
            $query->whereHas('rental.customer', function ($q) use ($data) {
                $q->where('name', 'like', '%' . $data['search'] . '%');
            });
        }

        if (!empty($data['date_from'])) {
            $query->whereDate('created_at', '>=', $data['date_from']);
        }

        if (!empty($data['date_to'])) {
            $query->whereDate('created_at', '<=', $data['date_to']);
        }

        return $query->get();
    }
}
