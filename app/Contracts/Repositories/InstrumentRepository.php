<?php

namespace App\Contracts\Repositories;

use App\Contracts\Interfaces\InstrumentInterface;
use App\Models\Instrument;

class InstrumentRepository extends BaseRepository implements InstrumentInterface
{
    public function __construct(Instrument $user)
    {
        $this->model = $user;
    }

    public function get()
    {
        return $this->model->get();
    }

    public function show(mixed $id)
    {
        return $this->model
            ->with(['category', 'brandCategory', 'reviews.customer', 'rentalDetails.rental.customer'])
            ->find($id);
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
            ->with(['category', 'brandCategory']);

        if (!empty($data['category'])) {
            $query->whereHas('category', function ($q) use ($data) {
                $q->where('name', $data['category']);
            });
        }

        if (!empty($data['min_price'])) {
            $query->where('price_per_day', '>=', $data['min_price']);
        }

        if (!empty($data['max_price'])) {
            $query->where('price_per_day', '<=', $data['max_price']);
        }

        if (!empty($data['status'])) {
            $query->where('status', $data['status']);
        }

        if (!empty($data['brand'])) {
            $query->whereHas('brandCategory', function ($q) use ($data) {
                $q->where('name', $data['brand']);
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
                $q->where('name', 'like', '%' . $data['search'] . '%');
            });
        }

        if (!empty($data['rent_date']) && !empty($data['return_date'])) {
            $rentDate = $data['rent_date'];
            $returnDate = $data['return_date'];

            $query->whereNotExists(function ($subQuery) use ($rentDate, $returnDate) {
                $subQuery->select('rental_details.id')
                    ->from('rental_details')
                    ->whereColumn('instruments.id', 'rental_details.instrument_id')
                    ->whereExists(function ($rentalSub) use ($rentDate, $returnDate) {
                        $rentalSub->select('rentals.id')
                            ->from('rentals')
                            ->whereColumn('rental_details.rental_id', 'rentals.id')
                            ->whereIn('rentals.status', ['reserved', 'approved', 'ongoing'])
                            ->where('rentals.rent_date', '<=', $returnDate)
                            ->where('rentals.return_date', '>=', $rentDate);
                    });
            });
        }


        return $query->paginate($perPage, ['*'], 'page', $page);
    }

    public function noPaginate(array $data): mixed
    {
        $query = $this->model->query()
            ->orderBy('updated_at', 'desc')
            ->with(['category', 'brand'])
            ->get();

        if (!empty($data['category'])) {
            $query->whereHas('category', function ($q) use ($data) {
                $q->where('name', $data['category']);
            });
        }

        if (!empty($data['min_price'])) {
            $query->where('price_per_day', '>=', $data['min_price']);
        }

        if (!empty($data['max_price'])) {
            $query->where('price_per_day', '<=', $data['max_price']);
        }

        if (!empty($data['status'])) {
            $query->where('status', $data['status']);
        }

        if (!empty($data['brand'])) {
            $query->where('brand', $data['brand']);
        }

        if (!empty($data['search'])) {
            $query->where(function ($q) use ($data) {
                $q->where('name', 'like', '%' . $data['search'] . '%');
            });
        }

        if (!empty($data['rent_date']) && !empty($data['return_date'])) {
            $rentDate = $data['rent_date'];
            $returnDate = $data['return_date'];

            $query->whereNotExists(function ($subQuery) use ($rentDate, $returnDate) {
                $subQuery->select('rental_details.id')
                    ->from('rental_details')
                    ->whereColumn('instruments.id', 'rental_details.instrument_id')
                    ->whereExists(function ($rentalSub) use ($rentDate, $returnDate) {
                        $rentalSub->select('rentals.id')
                            ->from('rentals')
                            ->whereColumn('rental_details.rental_id', 'rentals.id')
                            ->whereIn('rentals.status', ['reserved', 'approved', 'ongoing'])
                            ->where('rentals.rent_date', '<=', $returnDate)
                            ->where('rentals.return_date', '>=', $rentDate);
                    });
            });
        }

        return $query;
    }

    public function updateStatus($id, $status)
    {
        return $this->model->where('id', $id)->update(['status' => $status]);
    }
}
