<?php

namespace App\Exports;

use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ReviewsExport implements FromCollection, WithHeadings, WithMapping
{
    protected $data;
    private $index = 0;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function collection()
    {
        return $this->data;
    }

    public function headings(): array
    {
        return [
            'No',
            'Instrumen',
            'Customer',
            'Rating',
            'Komentar',
            'Tanggal'
        ];
    }

    public function map($item): array
    {
        return [
            ++$this->index,
            $item->instrument->name ?? '-',
            $item->customer->name ?? '-',
            $item->rating . '/5',
            $item->comment ?? '-',
            Carbon::parse($item->created_at)->format('d/m/Y'),
        ];
    }
}
