<?php

namespace App\Contracts\Repositories;

use App\Contracts\Interfaces\UserInterface;
use App\Models\User;

class UserRepository extends BaseRepository implements UserInterface
{
    public function __construct(User $user)
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
        return $this->show($id)->update($data);
    }

    public function delete(mixed $id)
    {
        return $this->show($id)->delete();
    }

    public function findByEmail(string $email)
    {
        return $this->model->where('email', $email)->first();
    }

    public function customPaginate(int $perPage = 10, int $page = 1, ?array $data): mixed
    {
        $query = $this->model->query()
            ->orderBy('updated_at', 'desc');

        if (!empty($data['role'])) {
            $query->role($data['role']);
        }

        if (!empty($data['date_from'])) {
            $query->whereDate('created_at', '>=', $data['date_from']);
        }

        if (!empty($data['date_to'])) {
            $query->whereDate('created_at', '<=', $data['date_to']);
        }

        if (!empty($data['search'])) {
            $query->where(function ($q) use ($data) {
                $q->where('name', 'like', '%' . $data['search'] . '%');
            });
        }

        return $query->paginate($perPage, ['*'], 'page', $page);
    }

    public function noPaginate(array $data): mixed
    {
        $query = $this->model->query()
            ->orderBy('updated_at', 'desc')
            ->get();

        return $query;
    }
}
