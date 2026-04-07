<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class PopularInstrumentsExport implements FromCollection, WithHeadings, WithMapping, WithColumnFormatting
{
    protected $data;
    private $index = 0;

    public function __construct($data)
    {
        $this->data = collect($data);
    }

    public function collection()
    {
        return $this->data;
    }

    public function headings(): array
    {
        return [
            'No',
            'Nama Instrumen',
            'Kategori',
            'Total Sewa',
            'Rerata Harga (IDR)',
            'Total Revenue (IDR)',
        ];
    }

    public function map($item): array
    {
        $item = (object) $item;
        return [
            ++$this->index,
            $item->name,
            $item->category_name,
            $item->rental_count,
            $item->avg_price,
            $item->total_revenue,
        ];
    }

    public function columnFormats(): array
    {
        return [
            'E' => '#,##0',
            'F' => '#,##0',
        ];
    }
}
