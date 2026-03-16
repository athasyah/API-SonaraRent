<?php

namespace App\Contracts\Repositories;

use App\Contracts\Interfaces\RentalInterface;
use App\Models\Rental;

class RentalRepository extends BaseRepository implements RentalInterface
{
    public function __construct(Rental $user)
    {
        $this->model = $user;
    }

    public function get()
    {
        return $this->model->get();
    }

    public function show(mixed $id)
    {
        return $this->model->with('details')->find($id);
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

        $rental = $this->show($id);
        if (!$rental) return false;

        $rental->details()->delete();

        return $rental->delete();
    }


    public function customPaginate(int $perPage = 10, int $page = 1, ?array $data): mixed
    {
        return $this->model->query()
            ->orderBy('updated_at', 'desc')
            ->with(['details', 'user', 'customer','penalty'])
            ->paginate($perPage, ['*'], 'page', $page);
    }

    public function noPaginate(array $data): mixed
    {
        $query = $this->model->query()
            ->orderBy('updated_at', 'desc')
            ->with(['details', 'user', 'customer'])
            ->get();
        return $query;
    }

    public function getByUser (string $id)
    {
        return $this->model->where('customer_id', $id)->with('details')->orderBy('updated_at', 'desc')->paginate(6);
    }

}
