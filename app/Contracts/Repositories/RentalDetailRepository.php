<?php

namespace App\Contracts\Repositories;

use App\Contracts\Interfaces\RentalDetailInterface;
use App\Enums\StatusEnum;
use App\Models\RentalDetail;

class RentalDetailRepository extends BaseRepository implements RentalDetailInterface
{
    public function __construct(RentalDetail $user)
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
        return $this->model->query()
            ->with(['instrument', 'rental'])
            ->orderBy('updated_at', 'desc')
            ->paginate($perPage, ['*'], 'page', $page);
    }

    public function noPaginate(array $data): mixed
    {
        $query = $this->model->query()
            ->orderBy('updated_at', 'desc')
            ->with(['instrument', 'rental'])
            ->get();
        return $query;
    }

    public function deleteByRentalId(string $rentalId)
    {
        return $this->model
            ->where('rental_id', $rentalId)
            ->delete();
    }
    public function instrumentExistsInRental(string $rentalId, string $instrumentId)
    {
        return $this->model->where('rental_id', $rentalId)
            ->where('instrument_id', $instrumentId)
            ->exists();
    }

    public function hasDateConflict(string $instrumentId, $rentDate, $returnDate): bool
    {
        return $this->model->where('instrument_id', $instrumentId)
            ->whereHas('rental', function ($query) use ($rentDate, $returnDate) {
                $query->whereIn('status', [
                    StatusEnum::RESERVED->value,
                    StatusEnum::ONGOING->value,
                ])
                    ->where('rent_date', '<=', $returnDate)
                    ->where('return_date', '>=', $rentDate);
            })
            ->lockForUpdate()
            ->exists();
    }

    public function getOverlappingByInstrumentIds(array $instrumentIds)
    {
        return $this->model->query()
            ->with(['rental', 'instrument'])
            ->whereIn('instrument_id', $instrumentIds)
            ->whereHas('rental', function ($q) {
                $q->whereIn('status', [
                    'reserved',
                    'approved',
                    'ongoing'
                ]);
            })
            ->get();
    }
}
