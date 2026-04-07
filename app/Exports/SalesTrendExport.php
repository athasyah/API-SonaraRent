<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class SalesTrendExport implements FromCollection, WithHeadings, WithMapping
{
    protected $data;
    protected $isMonthly;
    private $index = 0;

    public function __construct($data, $isMonthly = false)
    {
        $this->data = collect($data);
        $this->isMonthly = $isMonthly;
    }

    public function collection()
    {
        return $this->data;
    }

    public function headings(): array
    {
        return [
            'No',
            $this->isMonthly ? 'Bulan' : 'Tanggal',
            'Volume Penyewaan (Unit)',
        ];
    }

    public function map($item): array
    {
        $item = (object) $item;
        return [
            ++$this->index,
            $item->date_label,
            $item->count,
        ];
    }
}
