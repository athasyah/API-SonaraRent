<?php

namespace App\Contracts\Repositories;

use App\Contracts\Interfaces\ReviewInterface;
use App\Models\Review;

class ReviewRepository extends BaseRepository implements ReviewInterface
{
    public function __construct(Review $user)
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
            ->orderBy('updated_at', 'desc')
            ->with(['instrument', 'customer', 'rental']);

        if (!empty($data['search'])) {
            $query->where(function ($q) use ($data) {
                $q->whereHas('instrument', function ($iq) use ($data) {
                    $iq->where('name', 'like', '%' . $data['search'] . '%');
                })->orWhereHas('customer', function ($cq) use ($data) {
                    $cq->where('name', 'like', '%' . $data['search'] . '%');
                });
            });
        }

        if (!empty($data['rating'])) {
            $query->where('rating', $data['rating']);
        }

        if (!empty($data['instrument_id'])) {
            $query->where('instrument_id', $data['instrument_id']);
        }

        return $query->paginate($perPage, ['*'], 'page', $page);
    }

    public function noPaginate(array $data): mixed
    {
        $query = $this->model->query()
            ->with(['instrument', 'customer'])
            ->orderBy('updated_at', 'desc');

        if (!empty($data['search'])) {
            $query->where(function ($q) use ($data) {
                $q->whereHas('instrument', function ($iq) use ($data) {
                    $iq->where('name', 'like', '%' . $data['search'] . '%');
                })->orWhereHas('customer', function ($cq) use ($data) {
                    $cq->where('name', 'like', '%' . $data['search'] . '%');
                });
            });
        }

        if (!empty($data['instrument_id'])) {
            $query->where('instrument_id', $data['instrument_id']);
        }

        return $query->get();
    }

    public function existsByRentalAndInstrument(string $rentalId, string $instrumentId)
    {
        return $this->model->where('rental_id', $rentalId)
            ->where('instrument_id', $instrumentId)
            ->exists();
    }
}
