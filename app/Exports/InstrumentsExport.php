<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class InstrumentsExport implements FromCollection, WithHeadings, WithMapping
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
            'Nama',
            'Kategori',
            'Brand',
            'Harga/Hari',
            'Status'
        ];
    }

    public function map($item): array
    {
        return [
            ++$this->index,
            $item->name,
            $item->category->name ?? '-',
            $item->brand ?? '-',
            number_format($item->price_per_day, 0, ',', '.'),
            ucfirst($item->status),
        ];
    }
}
