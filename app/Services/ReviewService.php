<?php

namespace App\Services;

use App\Traits\UploadTrait;


class ReviewService
{
    use UploadTrait;
    public function mappingReview(array $data)
    {
        $mappedData = [
            'rental_id' => $data['rental_id'],
            'instrument_id' => $data['instrument_id'],
            'rating' => $data['rating'],
            'comment' => $data['comment'] ?? null,
            'customer_id' => auth()->id(),
        ];

        if (isset($data['image'])) {
            $mappedData['image'] = $this->upload('reviews', $data['image']);
        }

        return $mappedData;
    }
}
